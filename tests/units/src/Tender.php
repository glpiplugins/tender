<?php

namespace GlpiPlugin\Tender\tests\units;

use atoum;
use \DbTestCase;
use GlpiPlugin\Tender\Tender as TenderObjekt;

class Tender extends atoum {

    public function testgetTypeName() {
        $this->given($this->newTestedInstance('GlpiPlugin\Tender\Tender'))
            ->then->string($this->testedInstance->getTypeName())
            ->isEqualTo('Tenders');
    }

    public function testAdd() {
        $this->given($this->newTestedInstance('GlpiPlugin\Tender\Tender'))
            ->then->integer($this->testedInstance->add(
                [
                    'name' => 'Test Tender',
                    'tender_subject' => '2024-56-01',
                    'plugin_tender_tendertypes_id' => 1,
                    'plugin_tender_statuses_id' => 1,
                    'users_id' => 1,
                ]))
            ->isGreaterThan(0);
    }    

}
