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

Router::addRoute("/kaspi/buyItem","OrderController");

Router::addRoute("/kaspi/useItem","OrderController");

Router::addRoute("/kaspi/saveitem","ItemController");

Router::addRoute("/kaspi/listofnews","AdminController");

Router::addRoute("/kaspi/one_new","AdminController");

Router::addRoute("/kaspi/addnews","AdminController");

Router::addRoute("/kaspi/deletenews","AdminController");

Router::addRoute("/kaspi/savenews","AdminController");

Router::addRoute("/kaspi/allnews","NewsController");

Router::addRoute("/kaspi/order_call_back","OrderController");
