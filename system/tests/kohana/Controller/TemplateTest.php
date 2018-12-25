<?php
/**
 * Tests the Controller Template
 *
 * @package    Kohana
 * @category   Tests
 * @author     Koseven Team
 * @copyright  (c) Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Controller_TemplateTest extends Unittest_TestCase
{

    /**
     * Test creating a Template with a given Data String
     *
     * @test
     * @throws Request_Exception
     */
    public function setUp() {
        parent::setUp();

        // Expected Result inside our Response
        $expected = 'Default Template View! Test';

        // Need Request and Response Class to Mock abstract class Controller_Template
        $request = Kohana_Request::factory();
        $response = Kohana_Response::factory();

        // Mock Abstract Controller_Template Class
        $stubController = $this->getMockForAbstractClass('Kohana_Controller_Template', [$request, $response]);

        // Set Default Template to kohana/template
        $stubController->template = 'kohana' . DIRECTORY_SEPARATOR . 'template';

        // Call before function (NOTE: auto loading template is TRUE)
        $stubController->before();

        // Template should now be available and therefore a variable can be bind to it
        $stubController->template->data = 'Test';

        // Assign view to response body
        $stubController->after();

        // Compare Results
        $this->assertSame($expected, (string)$response);
    }

}
