<?

class AlpineTest extends PHPUnit_Framework_TestCase {

  public function testCreate() {
    $Test = Test::create(array(
      'text_field' => "I am text",
      'text_area' => "I am super long text"
    ));
    $this->assertGreaterThan(0, $Test->id);
    $this->assertEquals('varchar(255)', $Test::$types['text_field']);
    $this->assertEquals('text', $Test::$types['text_area']);
    $Test->delete();
  }

}

?>
