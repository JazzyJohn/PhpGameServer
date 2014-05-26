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

Router::addRoute("/kaspi/loadachive","AchivementController");

Router::addRoute("/kaspi/saveachive","AchivementController");

Router::addRoute("/kaspi/loadlvl","LevelController");

Router::addRoute("/kaspi/savelvl","LevelController");

Router::addRoute("/kaspi/returnAllStats","StatisticController");

Router::addRoute("/kaspi/loaditems","ItemController");

Router::addRoute("/kaspi/loadshop","ItemController");

Router::addRoute("/kaspi/buyItem","ItemController");

Router::addRoute("/kaspi/saveitem","ItemController");