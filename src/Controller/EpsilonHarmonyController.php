<?php

namespace Drupal\epsilon_harmony\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\ClientException;

/**
 * Controller routines for menu example routes.
 *
 * The response of Drupal's HTTP Kernel system's request is generated by
 * a piece of code called the controller.
 *
 * In Drupal 8, we use a controller class
 * for placing those piece of codes in methods which responds to a route.
 *
 * This file will be placed at {module_name}/src/Controller directory. Route
 * entries uses a key '_controller' to define the method called from controller
 * class.
 *
 * @see https://www.drupal.org/docs/8/api/routing-system/introductory-drupal-8-routes-and-controllers-example
 */
class EpsilonHarmonyController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'epsilon_harmony';
  }

  /**
   * Show a menu link in a menu other than the default "Navigation" menu.
   */
  public function testApi() {
    $service = \Drupal::service('epsilon_harmony.api_service');

    // Add record.
    // $record = [];
    // $record['CustomerKey'] = "Testing-darshan.choudhary@pfizer.com-08032021";
    // $record['FirstName'] = "Darshan";
    // $record['LastName'] = "choudhary";
    // $record['EmailAddress'] = "darshan.choudhary@pfizer.com";
    // $record['Profession'] = "IT";
    // $record['Province'] = "IN";
    // $response = $service->createRecord($record);

    // Delete record.
    // $response = $service->deleteRecord("Testing-darshan.choudhary@pfizer.com-08032021");

    // Retrieve record.
    // $response = $service->retrieveRecord("Testing-darshan.choudhary@pfizer.com-08032021");

    // Test API.
    // $response = $service->testApi();


    return $this->redirect('epsilon_harmony.logs');
  }
}