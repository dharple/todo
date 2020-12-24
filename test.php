<?php
use App\Helper;

require_once "vendor/autoload.php";

$entityManager = Helper::getEntityManager();

// test code

$itemManager = $entityManager->getRepository(App\Entity\Item::class);
$all = $itemManager->findAll();
foreach ($all as $item) {
    var_dump($item->getTask());
    var_dump($item->getUser()->getUsername());
}

//$item = new App\Entity\Item();
//$item
//    ->setTask('this is a test: ' . date('c'))
//    ->setCreated(new DateTime())
//    ->setPriority(10);
//$entityManager->persist($item);
//$entityManager->flush();
