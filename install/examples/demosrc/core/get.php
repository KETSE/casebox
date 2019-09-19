<?php
if (!empty($_GET['graph'])) {
    $graph = new Demosrc\Graph();
    $graph->load(
        array('path' => $_GET['graph'])
    );
}
