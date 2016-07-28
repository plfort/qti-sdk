<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\common\enums\BaseType;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\QtiComponentIterator;
use qtism\data\expressions\operators\Sum;
use qtism\data\expressions\BaseValue;

class QtiComponentIteratorTest extends QtiSmTestCase {
	
	public function testSimple() {
		$baseValues = new ExpressionCollection();
		$baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
		$baseValues[] = new BaseValue(BaseType::INTEGER, 25);
		$baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
		$sum = new Sum($baseValues);
		
		$iterator = new QtiComponentIterator($sum);
		
		$iterations = 0;
		foreach ($iterator as $k => $i) {
		    $this->assertSame($sum, $iterator->parent());
			$this->assertSame($baseValues[$iterations], $i);
			$this->assertSame($sum, $iterator->getCurrentContainer());
			$this->assertEquals($k, $i->getQtiClassName());
			$iterations++;
		}
		
		$this->assertSame(null, $iterator->parent());
	}

	public function testOneChildComponents() {
		$baseValues = new ExpressionCollection();
		$baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
		$sum = new Sum($baseValues);
		$iterator = new QtiComponentIterator($sum);

        // Iterate twice...
        for ($j = 0; $j < 2; $j++) {
            $iterations = 0;
            foreach ($iterator as $i) {
                $this->assertEquals('baseValue', $i->getQtiClassName());
                $iterations++;
            }
            $this->assertEquals(1, $iterations);
        }
	}
    
    public function testOneChildComponentsByClassName() {
        $baseValues = new ExpressionCollection();
		$baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
		$sum = new Sum($baseValues);
		$iterator = new QtiComponentIterator($sum, array('baseValue'));

		$iterations = 0;
		foreach ($iterator as $i) {
            $this->assertEquals('baseValue', $i->getQtiClassName());
			$iterations++;
		}
		$this->assertEquals(1, $iterations);
    }

	public function testNoChildComponents() {
		$baseValue = new BaseValue(BaseType::FLOAT, 10);
		$iterator = new QtiComponentIterator($baseValue);
		
		$this->assertFalse($iterator->valid());
		$this->assertSame($iterator->current(), null);
		
		// Just try to iterate again, just for fun...
		$iterator->next();
		$this->assertFalse($iterator->valid());
		$this->assertTrue($iterator->current() === null);
	}
	
	public function testAvoidRecursions() {
		$baseValues = new ExpressionCollection();
		$baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
		$baseValues[] = new BaseValue(BaseType::INTEGER, 25);
		$baseValues[] = new BaseValue(BaseType::FLOAT, 0.7);
		$baseValues[] = $baseValues[0]; // This could create a recursion issue.
		$baseValues[] = new BaseValue(BaseType::INTEGER, 0);
		
		$iterator = new QtiComponentIterator(new Sum($baseValues));
		
		$iterations = 0;
		foreach ($iterator as $k => $i) {
			$iterations++;
		}
		
		$this->assertEquals($iterations, 4);
	}
	
    /**
     * @dataProvider testClassSelectionProvider
     * 
     * @param integer $iterations
     * @param array $classNames
     */
	public function testClassSelection($file, $iterations, array $classNames) {
	    $doc = new XmlCompactDocument();
	    $doc->load($file);
	    
	    $iterator = new QtiComponentIterator($doc->getDocumentComponent(), $classNames);
        
        // We check that we can iterate twice, so that we are sure that the whole implementation
        // of Iterator is working well...
        $j = 0;
        for ($j = 0; $j < 2; $j++) {
            $i = 0;
	    
            foreach ($iterator as $responseProcessing) {
                $this->assertTrue(in_array($iterator->key(), $classNames));
                $i++;
            }
            
            $this->assertEquals($iterations, $i);
        }
	}
    
    public function testClassSelectionProvider() {
        $dir = self::samplesDir();
        
        return array(
            array("${dir}custom/runtime/itemsubset.xml", 7, array('responseProcessing')),
            array("${dir}custom/runtime/itemsubset.xml", 1, array('testPart')),
            array("${dir}custom/runtime/itemsubset.xml", 3, array('assessmentSection')),
            array("${dir}custom/runtime/itemsubset.xml", 11, array('responseProcessing', 'testPart','assessmentSection')),
            array("${dir}custom/runtime/itemsubset.xml", 15, array('outcomeDeclaration')),
            array("${dir}custom/runtime/itemsubset.xml", 0, array('x')),
            array("${dir}custom/runtime/itemsubset.xml", 0, array('x', 'y')),
        );
    }
}