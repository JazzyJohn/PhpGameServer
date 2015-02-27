<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:28
 */

Router::addRoute("/killedBy","StatisticController");

Router::addRoute("/killNpc","StatisticController");

Router::addRoute("/robotKilled","StatisticController");

Router::addRoute("/addUser","StatisticController");

Router::addRoute("/globalerrorlog","StatisticController");

Router::addRoute("/returnAllStats","StatisticController");

Router::addRoute("/notifyUsers","StatisticController");

Router::addRoute("/loadachive","AchivementController");
 
Router::addRoute("/saveachive","AchivementController");

Router::addRoute("/loadlvl","LevelController");

Router::addRoute("/savelvl","LevelController");

Router::addRoute("/returnAllStats","StatisticController");

Router::addRoute("/loaditems","ItemController");

Router::addRoute("/loadshop","ItemController");

Router::addRoute("/saveitem","ItemController");

Router::addRoute("/loaditems","ItemControllerOld");

Router::addRoute("/loadshop","ItemControllerOld");

Router::addRoute("/saveitem","ItemControllerOld");

Router::addRoute("/loaditemsnew","ItemController");

Router::addRoute("/loadshopnew","ItemController");

Router::addRoute("/saveitemnew","ItemController");

Router::addRoute("/markitem","ItemController");

Router::addRoute("/disentegrateItem","OrderController");

Router::addRoute("/buyItem","OrderController");

Router::addRoute("/useItem","OrderController");

Router::addRoute("/repairItem","OrderController");

Router::addRoute("/listofnews","AdminController");

Router::addRoute("/one_new","AdminController");

Router::addRoute("/addnews","AdminController");

Router::addRoute("/deletenews","AdminController");

Router::addRoute("/savenews","AdminController");

Router::addRoute("/stats","AdminController");

Router::addRoute("/allnews","NewsController");

Router::addRoute("/order_call_back","OrderController");

Router::addRoute("/chargedata","ItemController");

Router::addRoute("/loadmoneyreward","RewardController");

Router::addRoute("/syncmoneyreward","RewardController");

Router::addRoute("/registration","RegistrationAPI");

Router::addRoute("/login","RegistrationAPI");

Router::addRoute("/doublereward","PremiumController");

Router::addRoute("/lowerstamina","PremiumController");

Router::addRoute("/dayly","CronController");

Router::addRoute("/saveoperation","TournamentController");

Router::addRoute("/operations","AdminController");

Router::addRoute("/daylyTask","AchivementController");

Router::addRoute("/finishTask","AchivementController");

Router::addRoute("/skipTask","AchivementController");

Router::addRoute("/statisticdata","StatisticController");
