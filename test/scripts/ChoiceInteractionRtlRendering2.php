<?php

use qtism\runtime\common\State;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\TemplateVariable;
use qtism\data\storage\xml\XmlDocument;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

$doc = new XmlDocument();
$doc->load(dirname(__FILE__) . '/../samples/rendering/choiceinteractionrtl_2.xml');

$renderer = new XhtmlRenderingEngine();
$rendering = $renderer->render($doc->getDocumentComponent());
$rendering->formatOutput = true;

echo $rendering->saveXML();