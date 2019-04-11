<?php

namespace Drupal\epsilon_harmony\Services;

use Exception as Exception;
use Drupal\Core\Form\ConfigFormBase;
use GuzzleHttp\Exception\ClientException;
use Drupal\Component\Serialization\Json;
use Drupal\epsilon_harmony\StatusCodes;
use Drupal\epsilon_harmony\Services\EpsilonConnectionFactory;

/**
 * Description for API Factory of Harmony API.
 *
 * @author Darshan Choudhary
 */

class EpsilonApiFactory extends EpsilonConnectionFactory {
  /**
   * Get a new access token or an existing one for the API.
   *
   * @return string
   *   Returns the access token.
   */
  public static function getToken($object, $test = FALSE)
  {
    if ($test == TRUE || ($object->getTokenTimeout() == NULL || (time() - $object->getTokenTimeout() > 3600))) {
      try {
        $url = $object->tokenUrl . '/Epsilon/oauth2/access_token';

        // Form data to be passed.
        $data['username']   = $object->getUsername();
        $data['password']   = $object->getPassword();
        $data['scope']      = 'cn mail sn givenname uid employeeNumber';
        $data['grant_type'] = 'password';

        $headers = [
          'Authorization' => 'Basic ' . $object->getBaseToken(),
          // 'Authorization' => 'Basic ddd',
          'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $response = \Drupal::httpClient()->post($url, [
          'headers' => $headers,
          'form_params' => $data
        ]);

        $response_data_json = $response->getBody()->getContents();
        $response_data = (array) \GuzzleHttp\json_decode($response_data_json);

        $values = [
          'endpoint' => $url,
          'uid' => \Drupal::currentUser()->id(),
          'method' => 'POST',
          'status_code' => $response->getStatusCode(),
          'status_message'=> $response->getReasonPhrase(),
          'header'=> JSON::encode($headers),
          'request'=> JSON::encode($data),
          'response' => JSON::encode($response_data)
        ];
        self::logEpsilon($values);

        if ($response->getStatusCode() == StatusCodes::HTTP_OK && !empty($response_data)) {
          $config = \Drupal::service('config.factory')->getEditable('epsilon_harmony.settings');
          $config->set('epsilon_harmony_token_timeout',  time())->save();
          $config->set('epsilon_harmony_access_token',  $response_data['access_token'])->save();

          drupal_set_message(t('All OK! Please check the logs for more details.'), 'status', TRUE);

          return $response_data['access_token'];
        }
        else {
          throw new Exception('Something went wrong while fetching the token. Please check the logs for further details.');
        }
      } catch (Exception $e) {
        $response = (string) $e->getResponse()->getBody();
        $values = [
          'endpoint' => $url,
          'uid' => \Drupal::currentUser()->id(),
          'method' => 'POST',
          'status_code' => $e->getCode(),
          'status_message'=> JSON::decode($response)['fault']['faultstring'],
          'header'=> JSON::encode($headers),
          'request'=> JSON::encode($data),
          'response' => $response
        ];
        $log = self::logEpsilon($values);
        drupal_set_message(t('Failure!! Please check the epsilon log @link for further details.', [
          '@link' => $log->toLink()->toString(),
        ]), 'error', TRUE);

        return NULL;
      }
    }
    else {
      return self::$accessToken;
    }
  }

  /**
   * Created a record on Epsilon DB by calling the API.
   *
   * @param array $record
   *   Data to be passed through API.
   *
   * @return array
   *   Returns the response recieved from the API.
   */
  public function createRecord($record)
  {
    try {
      $url = $this->apiUrl . "/v4/profiles/records";

      $headers = [
        'Authorization' => 'Bearer ' . self::getToken($this),
        'X-OUID' => $this->getXouid(),
        'Content-Type' => 'application/json',
      ];

      // HTTP Form request.
      $response = \Drupal::httpClient()->post($url, [
        'headers' => $headers,
        'json' => $record
      ]);

      $response_data_json = $response->getBody()->getContents();
      $response_data = (array) \GuzzleHttp\json_decode($response_data_json);

      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'POST',
        'status_code' => $response->getStatusCode(),
        'status_message'=> $response->getReasonPhrase(),
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => JSON::encode($response_data)
      ];
      $log = self::logEpsilon($values);

      if ($response->getStatusCode() == StatusCodes::HTTP_OK && !empty($response_data)) {
        drupal_set_message(t('Record successfully created. Please check the epsilon log @link for further details.', [
          '@link' => $log->toLink()->toString(),
        ]), 'status', TRUE);
        return $response_data;
      }
      else {
        throw new Exception('Create record failed. Please check the logs for further details.');
      }
    }
    catch (ClientException $e) {
      $response = (string) $e->getResponse()->getBody();
      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'POST',
        'status_code' => $e->getCode(),
        'status_message'=> JSON::decode($response)['resultCode'],
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => $response
      ];
      $log = self::logEpsilon($values);

      drupal_set_message(t('Create record failed. Please check the log @link for further details.', [
        '@link' => $log->toLink()->toString(),
      ]), 'error', TRUE);

      \Drupal::logger('type')->error($e->getMessage());
    }
  }

  /**
   * Updates the record on Epsilon DB by calling the API.
   *
   * @param array $record
   *   Data to be passed through API.
   *
   * @return array
   *   Returns the response recieved from the API.
   */
  public function updateRecord($record)
  {
    try {
      $url = $this->apiUrl . "/v4/profiles/records/" . $record['CustomerKey'];

      $headers = [
        'Authorization' => 'Bearer ' . self::getToken($this),
        'X-OUID' => $this->getXouid(),
        'Content-Type' => 'application/json',
      ];

      // HTTP Form request.
      $response = \Drupal::httpClient()->put($url, [
        'headers' => $headers,
        'json' => $record
      ]);

      $response_data_json = $response->getBody()->getContents();
      $response_data = (array) \GuzzleHttp\json_decode($response_data_json);

      // Log the API.
      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'PUT',
        'status_code' => $response->getStatusCode(),
        'status_message'=> $response->getReasonPhrase(),
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => JSON::encode($response_data)
      ];
      $log = self::logEpsilon($values);

      if ($response->getStatusCode() == StatusCodes::HTTP_OK && !empty($response_data)) {
        drupal_set_message(t('Record successfully updated. Please check the epsilon log @link for further details.', [
          '@link' => $log->toLink()->toString(),
        ]), 'status', TRUE);
        return $response_data;
      }
      else {
        throw new Exception('Update record failed. Please check the epsilon logs for further details.');
      }
    }
    catch (ClientException $e) {
      $response = (string) $e->getResponse()->getBody();
      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'PUT',
        'status_code' => $e->getCode(),
        'status_message'=> JSON::decode($response)['resultCode'],
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => $response
      ];
      $log = self::logEpsilon($values);

      drupal_set_message(t('Record update failed. Please check the epsilon log @link for further details.', [
        '@link' => $log->toLink()->toString(),
      ]), 'status', TRUE);

      \Drupal::logger('type')->error($e->getMessage());
    }
  }

  /**
   * Deletes the record on Epsilon DB by calling the API.
   *
   * @param array $record
   *   Data to be passed through API.
   *
   * @return array
   *   Returns the response recieved from the API.
   */
  public function deleteRecord($customer_key)
  {
    try {
      $url = $this->apiUrl . "/v4/profiles/records/" . $customer_key;

      $headers = [
        'Authorization' => 'Bearer ' . self::getToken($this),
        'X-OUID' => $this->getXouid(),
        'Content-Type' => 'application/json',
      ];

      // HTTP Form request.
      $response = \Drupal::httpClient()->delete($url, [
        'headers' => $headers,
      ]);

      $response_data_json = $response->getBody()->getContents();
      $response_data = (array) \GuzzleHttp\json_decode($response_data_json);

      // Log the API.
      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'DELETE',
        'status_code' => $response->getStatusCode(),
        'status_message'=> $response->getReasonPhrase(),
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => JSON::encode($response_data)
      ];
      $log = self::logEpsilon($values);

      if ($response->getStatusCode() == StatusCodes::HTTP_OK && !empty($response_data)) {
        drupal_set_message(t('Record successfully deleted. Please check the epsilon log @link for further details.', [
          '@link' => $log->toLink()->toString(),
        ]), 'status', TRUE);
        return $response_data;
      }
      else {
        throw new Exception('Record deletion failed. Please check the epsilon logs for further details.');
      }
    }
    catch (ClientException $e) {
      $response = (string) $e->getResponse()->getBody();
      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'DELETE',
        'status_code' => $e->getCode(),
        'status_message'=> JSON::decode($response)['resultCode'],
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => $response
      ];
      $log = self::logEpsilon($values);

      drupal_set_message(t('Record deletion failed. Please check the epsilon log @link for further details.', [
        '@link' => $log->toLink()->toString(),
      ]), 'status', TRUE);

      \Drupal::logger('type')->error($e->getMessage());
    }
  }

  /**
   * Retrieves the record on Epsilon DB by calling the API.
   *
   * @param array $record
   *   Data to be passed through API.
   *
   * @return array
   *   Returns the response recieved from the API.
   */
  public function retrieveRecord($customer_key)
  {
    try {
      $url = $this->apiUrl . "/v4/profiles/records/" . $customer_key;

      $headers = [
        'Authorization' => 'Bearer ' . self::getToken($this),
        'X-OUID' => $this->getXouid(),
        'Content-Type' => 'application/json',
      ];

      // HTTP Form request.
      $response = \Drupal::httpClient()->get($url, [
        'headers' => $headers,
      ]);

      $response_data_json = $response->getBody()->getContents();
      $response_data = (array) \GuzzleHttp\json_decode($response_data_json);

      // Log the API.
      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'GET',
        'status_code' => $response->getStatusCode(),
        'status_message'=> $response->getReasonPhrase(),
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => JSON::encode($response_data)
      ];
      $log = self::logEpsilon($values);

      if ($response->getStatusCode() == StatusCodes::HTTP_OK && !empty($response_data)) {
        drupal_set_message(t('Record retrieved successfully. Please check the epsilon log @link for further details.', [
          '@link' => $log->toLink()->toString(),
        ]), 'status', TRUE);
        return $response_data;
      }
      else {
        throw new Exception('Record retrieve failed. Please check the epsilon logs for further details.');
      }
    }
    catch (ClientException $e) {
      $response = (string) $e->getResponse()->getBody();
      $values = [
        'endpoint' => $url,
        'uid' => \Drupal::currentUser()->id(),
        'method' => 'GET',
        'status_code' => $e->getCode(),
        'status_message'=> JSON::decode($response)['resultCode'],
        'header'=> JSON::encode($headers),
        'request'=> JSON::encode($record),
        'response' => $response
      ];
      $log = self::logEpsilon($values);

      drupal_set_message(t('Record retrieve failed. Please check the epsilon log @link for further details.', [
        '@link' => $log->toLink()->toString(),
      ]), 'status', TRUE);

      \Drupal::logger('type')->error($e->getMessage());
    }
  }

  /**
   * Checks if the configurations are valid by making a token call.
   *
   * @return string
   *   Return the access key for testing.
   */
  public function testApi()
  {
    return self::getToken($this, TRUE);
  }

  /**
   * Logs the epsilon API call in DB.
   *
   * @param array $record
   *   Array of the record to be logged.
   *
   * @return object
   *   Return the log created.
   */
  public function logEpsilon($record = [])
  {
    $log = entity_create('epsilon_harmony_log', $record);
    $log->save();
    // $log->toLink()->toString()
    return $log;
  }
}
