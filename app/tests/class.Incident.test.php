<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Incident.php');


class TestOfIncidentClass extends UnitTestCase {
    
    function setUp() {
        $this->incident = new Incident();
    }
    
    function tearDown() {
        $this->incident->delete();
    }
    
    function testLocationClass() {
        $this->assertIsA($this->incident, 'Incident');
    }
    
    function testId() {
        $this->assertEqual($this->incident->get_id(), 0);
    }
    
    function testTitle() {
        $name = 'Test';
        $this->incident->set_title($name);
        $this->assertEqual($this->incident->get_title(), $name);
    }
    
    function testGetAddAlterForm() {
    	$this->assertTrue($this->incident->get_add_alter_form());
    }
    
    function testMonth() {
    	$this->assertFalse($this->incident->set_month(13));
        $this->assertTrue($this->incident->set_month(1));
        $this->assertEqual($this->incident->get_month(), 1);
    }
    
    function testYear() {
    	$this->incident->set_year(2013);
        $this->assertEqual($this->incident->get_year(), 2013);
    }
    
    function testAgentId() {
        $this->assertEqual($this->incident->get_agent_id(), 0);
    	$this->incident->set_agent_id(1);
        $this->assertEqual($this->incident->get_agent_id(), 1);
    }
    
    function testActionId() {
    	$this->assertEqual($this->incident->get_action_id(), 0);
        $this->incident->set_action_id(1);
        $this->assertEqual($this->incident->get_action_id(), 1);
    }
    
    function testAssetId() {
        $this->assertEqual($this->incident->get_asset_id(), 0);
        $this->assertFalse($this->incident->set_asset_id(-1));
        $this->incident->set_asset_id(1);
        $this->assertEqual($this->incident->get_asset_id(), 1);	
    }
    
    function testConfidentialData() {
    	$this->assertEqual($this->incident->get_confidential_data(), 0);
        $this->incident->set_confidential_data(0);
        $this->assertFalse($this->incident->get_confidential_data());
        $this->incident->set_confidential_data(1);
        $this->assertTrue($this->incident->get_confidential_data());
    }
    
    function testIntegrityLoss() {
    	$text = 'Some text.';
        $illegal_text = '<script>alert(document.cookie);</script>';
        $this->incident->set_integrity_loss($text);
        $this->assertEqual($this->incident->get_integrity_loss(), $text);
        $this->incident->set_integrity_loss($illegal_text);
        $this->assertNotEqual($this->incident->get_integrity_loss(), $illegal_text);
    }
    
    function testAuthenticityLoss() {
        $text = 'Some text.';
        $illegal_text = '<script>alert(document.cookie);</script>';
        $this->incident->set_authenticity_loss($text);
        $this->assertEqual($this->incident->get_authenticity_loss(), $text);
        $this->incident->set_authenticity_loss($illegal_text);
        $this->assertNotEqual($this->incident->get_authenticity_loss(), $illegal_text);
        $this->assertEqual($this->incident->get_authenticity_loss(), htmlspecialchars($illegal_text));
    }
    
    function testUtilityLoss() {
        $text = 'Some text.';
        $illegal_text = '<script>alert(document.cookie);</script>';
        $this->incident->set_utility_loss($text);
        $this->assertEqual($this->incident->get_utility_loss(), $text);
        $this->incident->set_utility_loss($illegal_text);
        $this->assertNotEqual($this->incident->get_utility_loss(), $illegal_text);
        $this->assertEqual($this->incident->get_utility_loss(), htmlspecialchars($illegal_text));
    }
    
    function testGetCollectionDefinition() {
        $this->assertIsA($this->incident->get_collection_definition(), 'String');
    }
    
    function testAvailityLossTimeframe() {
        $tframe = new IRTimeframe();
        $dur = 'A long time.';
        $tframe->set_duration($dur);
        $tframe->save();
        $id = $tframe->get_id();
    	$this->assertTrue($this->incident->set_availability_loss_timeframe_id($id));
        $this->assertEqual($this->incident->get_availability_loss_timeframe_id(), $id);
        $this->assertEqual($this->incident->get_availability_loss_timeframe_friendly(), $dur);
        $tframe->delete();
    }
    
    function testActionToDiscoveryTimeframe() {
        $tframe = new IRTimeframe();
        $dur = 'A long time.';
        $tframe->set_duration($dur);
        $tframe->save();
        $id = $tframe->get_id();
        $this->assertTrue($this->incident->set_action_to_discovery_timeframe_id($id));
        $this->assertEqual($this->incident->get_action_to_discovery_timeframe_id(), $id);
        $this->assertEqual($this->incident->get_action_to_discovery_timeframe_friendly(), $dur);
        $tframe->delete();
    }
    
    function testDiscoveryToContainmentTimeframe() {
        $tframe = new IRTimeframe();
        $dur = 'A long time.';
        $tframe->set_duration($dur);
        $tframe->save();
        $id = $tframe->get_id();
        $this->assertTrue($this->incident->set_discovery_to_containment_timeframe_id($id));
        $this->assertEqual($this->incident->get_discovery_to_containment_timeframe_id(), $id);
        $this->assertEqual($this->incident->get_discovery_to_containment_timeframe_friendly(), $dur);
        $tframe->delete();
    }
    
    function testDiscoveryMethod() {
    	$this->assertTrue($this->incident->set_discovery_id(1));
        $this->assertEqual($this->incident->get_discovery_id(), 1);
    }
    
    function testDiscoveryEvidenceSources() {
    	$text = 'Some text.';
        $illegal_text = '<script>alert(document.cookie);</script>';
        $this->incident->set_discovery_evidence_sources($text);
        $this->assertEqual($this->incident->get_discovery_evidence_sources(), $text);
        $this->incident->set_discovery_evidence_sources($illegal_text);
        $this->assertNotEqual($this->incident->get_discovery_evidence_sources(), $illegal_text);
        $this->assertEqual($this->incident->get_discovery_evidence_sources(), htmlspecialchars($illegal_text));
    }
    
    function testDiscoveryMetrics() {
        $text = 'Some text.';
        $illegal_text = '<script>alert(document.cookie);</script>';
        $this->incident->set_discovery_metrics($text);
        $this->assertEqual($this->incident->get_discovery_metrics(), $text);
        $this->incident->set_discovery_metrics($illegal_text);
        $this->assertNotEqual($this->incident->get_discovery_metrics(), $illegal_text);
        $this->assertEqual($this->incident->get_discovery_metrics(), htmlspecialchars($illegal_text));
    }
    
    function testAssetLossMagnitude() {
        $mag = new IRMagnitude();
        $name = 'Magnanimous';
        $mag->set_name($name);
        $mag->save();
        $id = $mag->get_id();
    	$this->assertTrue($this->incident->set_asset_loss_magnitude_id($id));
        $this->assertEqual($this->incident->get_asset_loss_magnitude_id(), $id);
        $this->assertEqual($this->incident->get_asset_loss_magnitude_friendly(), $name);
        $mag->delete();
    }
    
    function testDisruptionMagnitude() {
        $mag = new IRMagnitude();
        $name = 'Magnanimous';
        $mag->set_name($name);
        $mag->save();
        $id = $mag->get_id();
    	$this->assertTrue($this->incident->set_disruption_magnitude_id($id));
        $this->assertEqual($this->incident->get_disruption_magnitude_id(), $id);
        $this->assertEqual($this->incident->get_disruption_magnitude_friendly(), $name);
        $mag->delete();
    }
    
    function testResponseCostMagnitude() {
        $mag = new IRMagnitude();
        $name = 'Magnanimous';
        $mag->set_name($name);
        $mag->save();
        $id = $mag->get_id();
        $this->assertTrue($this->incident->set_response_cost_magnitude_id($id));
        $this->assertEqual($this->incident->get_response_cost_magnitude_id(), $id);
        $this->assertEqual($this->incident->get_response_cost_magnitude_friendly(), $name);
        $mag->delete();
    }
    
    function testImpactMagnitude() {
        $mag = new IRMagnitude();
        $name = 'Magnanimous';
        $mag->set_name($name);
        $mag->save();
        $id = $mag->get_id();
        $this->assertTrue($this->incident->set_impact_magnitude_id($id));
        $this->assertEqual($this->incident->get_impact_magnitude_id(), $id);
        $this->assertEqual($this->incident->get_impact_magnitude_friendly(), $name);
        $mag->delete();
    }
    
    function testHindsight() {
        $text = 'Some text.';
        $illegal_text = '<script>alert(document.cookie);</script>';
        $this->incident->set_hindsight($text);
        $this->assertEqual($this->incident->get_hindsight(), $text);
        $this->incident->set_hindsight($illegal_text);
        $this->assertNotEqual($this->incident->get_hindsight(), $illegal_text);
        $this->assertEqual($this->incident->get_hindsight(), htmlspecialchars($illegal_text));
    }
    
    function testCorrectionRecommended() {
        $text = 'Some text.';
        $illegal_text = '<script>alert(document.cookie);</script>';
        $this->incident->set_correction_recommended($text);
        $this->assertEqual($this->incident->get_correction_recommended(), $text);
        $this->incident->set_correction_recommended($illegal_text);
        $this->assertNotEqual($this->incident->get_correction_recommended(), $illegal_text);
        $this->assertEqual($this->incident->get_correction_recommended(), htmlspecialchars($illegal_text));
    }
    
    function testSaveDelete() {
        $this->incident->set_title('Test');
        $this->incident->set_year(2013);
        $this->incident->set_month(1);
        $this->incident->set_agent_id(1);
        $this->incident->set_action_id(1);
        $this->incident->set_asset_id(1);
        $this->incident->set_confidential_data(1);
        $this->incident->set_integrity_loss('Integrity loss text.');
        $this->incident->set_authenticity_loss('Authenticity loss text.');
        $this->incident->set_utility_loss('Utility loss text.');
        $this->incident->set_availability_loss_timeframe_id(1);
        $this->incident->set_action_to_discovery_timeframe_id(1);
        $this->incident->set_discovery_to_containment_timeframe_id(1);
        $this->incident->set_discovery_id(1);
        $this->incident->set_discovery_evidence_sources('Evidence sources text.');
        $this->incident->set_discovery_metrics('Discovery metrics text.');
        $this->incident->set_asset_loss_magnitude_id(1);
        $this->incident->set_disruption_magnitude_id(1);
        $this->incident->set_response_cost_magnitude_id(1);
        $this->incident->set_impact_magnitude_id(1);
        $this->incident->set_hindsight('Hindsight text.');
        $this->incident->set_correction_recommended('Correction recommended.');
        $this->assertTrue($this->incident->save());
        $this->assertTrue($this->incident->get_id() > 0 );
        
        $id = $this->incident->get_id();
        // Test retrieval and update
        $newincident = new Incident($id);
        $this->assertEqual($newincident->get_title(), 'Test');
        $this->assertEqual($newincident->get_availability_loss_timeframe_id(), 1);
        $newincident->set_title('Test2');
        $this->assertTrue($newincident->save());
        
        // Clean up
        $this->assertTrue($this->incident->delete());
    }
}
?>