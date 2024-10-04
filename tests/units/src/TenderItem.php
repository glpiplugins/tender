<?php

namespace GlpiPlugin\Tender\tests\units;

use atoum;
use \DbTestCase;

class TenderItem extends atoum {

    public function testgetTypeName() {
        $this->given($this->newTestedInstance('GlpiPlugin\Tender\TenderItem'))
            ->then->string($this->testedInstance->getTypeName())
            ->isEqualTo('Tender Item');
    }

    // public function testAdd() {
    //     $this->given($this->newTestedInstance('GlpiPlugin\Tender\Tender'))
    //         ->then->integer($this->testedInstance->add(['name' => 'Test Tender']))
    //         ->isGreaterThan(0);
    // }    

}
