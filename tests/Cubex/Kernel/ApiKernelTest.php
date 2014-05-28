<?php

class ApiKernelTest extends CubexTestCase
{
  /**
   * @param $path
   * @param $expect
   * @param $errMsg
   * @param $errNo
   *
   * @dataProvider handleProvider
   */
  public function testHandle($path, $expect, $errMsg, $errNo)
  {
    $request = \Cubex\Http\Request::create($path);
    $kernel  = new ApiTestKernel();
    $cubex   = $this->newCubexInstace();
    $cubex->instance('\Cubex\Routing\IRouter', new \Cubex\Routing\Router());
    $kernel->setCubex($cubex);
    $response = $kernel->handle(
      $request,
      \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST,
      true
    );

    if($response instanceof \Cubex\Http\Response)
    {
      $apiObject = $response->getOriginalResponse();
      $this->assertObjectHasAttribute('error', $apiObject);
      $this->assertObjectHasAttribute('result', $apiObject);
      $this->assertEquals($errMsg, $apiObject->error->message);
      $this->assertEquals($errNo, $apiObject->error->code);
      $this->assertEquals($expect, $apiObject->result);
    }
  }

  public function handleProvider()
  {
    return [
      [
        '/testSuccess',
        ["username" => 'brooke', 'name' => 'Brooke Bryan'],
        '',
        200
      ],
      ['/testError', '', 'File not found', 404],
      ['/testErrorCodeless', '', 'Missing code', 500],
      ['/testNonCubexResponse', 'Strange Content', '', 200],
    ];
  }

  public function testSubRoutes()
  {
    $apiTestKernel = new ApiTestKernel();
    $this->assertEquals(
      [
        '%s',
        '%sController',
        '%s\%sController',
        'ApiTestKernel\%s'
      ],
      $apiTestKernel->subRouteTo()
    );
  }
}

class ApiTestKernel extends \Cubex\Kernel\ApiKernel
{
  public function testSuccess()
  {
    return ["username" => 'brooke', 'name' => 'Brooke Bryan'];
  }

  public function testError()
  {
    throw new Exception('File not found', 404);
  }

  public function testErrorCodeless()
  {
    throw new Exception('Missing code');
  }

  public function testNonCubexResponse()
  {
    return new \Symfony\Component\HttpFoundation\Response('Strange Content');
  }
}
