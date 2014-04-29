<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:28
 */

Router::addRoute("/kaspi/killedBy","StatisticController");
Router::addRoute("/kaspi/robotKilled","StatisticController");

Router::addRoute("/kaspi/addUser","StatisticController");

Router::addRoute("/kaspi/returnAllStats","StatisticController");

Router::addRoute("/kaspi/notifyUsers","StatisticController");