<?php
namespace Cubex\Http;

use Cubex\Context\Context;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class LazyHandler extends FuncHandler
{
  /**
   * @param Context $c
   *
   * @return Response
   * @throws Exception
   */
  public function handle(Context $c): Response
  {
    $result = call_user_func($this->_func, $c);
    if($result instanceof Handler)
    {
      return $result->handle($c);
    }
    else if($result instanceof Response)
    {
      return $result;
    }
    throw new Exception("invalid lazy handler response " . gettype($result), 500);
  }
}