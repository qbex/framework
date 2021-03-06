<?php
namespace Cubex\I18n;

use Cubex\Cubex;
use Cubex\CubexAware;
use Cubex\CubexAwareTrait;
use Cubex\Events\ShutdownEvent;
use Packaged\I18n\Catalog\DynamicArrayCatalog;
use Packaged\I18n\Catalog\Message;
use Packaged\I18n\Catalog\MessageCatalog;
use Packaged\I18n\Translators\TranslationLogger;
use Packaged\I18n\Translators\Translator;

class TranslationUpdater implements CubexAware
{
  use CubexAwareTrait;

  /**
   * @var MessageCatalog
   */
  protected $_catalog;
  protected $_file;
  protected $_lang;

  public function __construct(Cubex $cubex, MessageCatalog $catalog, $file, $lang, TranslationLogger $translationLogger = null)
  {
    $this->_catalog = $catalog;
    $this->_file = $file;
    $this->_lang = $lang;
    $this->setCubex($cubex);
    $cubex->listen(
      ShutdownEvent::class,
      function () use ($translationLogger) {
        if ($translationLogger !== null)
        {
          $this->storeTranslations($translationLogger);
        }
        else
        {
          $translator = $this->getCubex()->retrieve(Translator::class);
          if($translator instanceof TranslationLogger)
          {
            $this->storeTranslations($translator);
          }
        }

      }
    );
  }

  public function storeTranslations(TranslationLogger $logger)
  {
    if($this->_catalog instanceof DynamicArrayCatalog)
    {
      $updated = false;
      foreach((array)$logger->getTranslations() as $mid => $trans)
      {
        $msg = $this->_catalog->getMessage($mid);
        if($msg === null)
        {
          $updated = true;
          $this->_catalog->addMessage($mid, Message::fromDefault($trans[TranslationLogger::KEY_DEFAULT])->getOptions());
        }
      }
      if($updated)
      {
        $this->writeCatalog($this->_catalog);
      }
    }
  }

  public function writeCatalog(DynamicArrayCatalog $catalog)
  {
    $catalog->setTranslationCallback(function ($text) { return $this->_getTranslation($text, $this->_lang); });
    $this->_writeFile($this->_file, $catalog->asPhpFile(true));
  }

  protected function _writeFile($filename, $data)
  {
    $dir = dirname($filename);
    if(!file_exists($dir))
    {
      mkdir($dir, 0777, true);
    }
    file_put_contents($filename, $data);
    if(function_exists('opcache_reset'))
    {
      opcache_reset();
    }
  }

  protected function _getTranslation($text, $language)
  {
    //Override this method if you want to add in your own translations e.g. Google Translate
    return $text;
  }

}
