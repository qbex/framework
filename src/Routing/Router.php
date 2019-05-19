<?php
namespace Cubex\Routing;

use Packaged\Context\Context;
use Cubex\Http\FuncHandler;
use Cubex\Http\Handler;

class Router extends RouteProcessor
{
  /**
   * @var ConditionHandler[]
   */
  protected $_conditions = [];
  protected $_defaultHandler;

  public static function i()
  {
    return new static();
  }

  public function setDefaultHandler(Handler $handler)
  {
    $this->_defaultHandler = $handler;
    return $this;
  }

  public function onPathFunc($path, callable $handleFunc): Condition
  {
    return $this->onPath($path, new FuncHandler($handleFunc));
  }

  public function onPath($path, Handler $handler): Condition
  {
    $condition = RequestConstraint::i()->path($path);
    $this->addCondition(Route::with($condition)->setHandler($handler));
    return $condition;
  }

  public function addCondition(ConditionHandler $condition): Router
  {
    $this->_conditions[] = $condition;
    return $this;
  }

  public function getHandler(Context $context)
  {
    return parent::_getHandler($context);
  }

  protected function _generateRoutes()
  {
    foreach($this->_conditions as $condition)
    {
      yield $condition;
    }
    return $this->_defaultHandler;
  }
}
