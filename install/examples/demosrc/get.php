<?php
if (!empty($_GET['graph'])) {
    $graph = new Demosrc\Graph();
    $graph->load(
        (object) array('caseId' => $_GET['graph'])
    );
}
