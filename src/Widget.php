<?php

namespace mmaurice\widgets;

class Widget
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function execute()
    {
        if (!isset($_GET['name'])) {
            die('Widget name not receive');
        }

        $name = explode(':', $_GET['name']);

        if (!in_array($name[0], ['chunk', 'snippet'])) {
            die('Widget name not have prefix');
        }

        if (count($name) < 2) {
            die('Widget name is not valid');
        }

        die($this->load($name[0], $name[1]));
    }

    protected function load($type, $name)
    {
        global $modx;

        $content = 'Element not available';

        if ($this->checkAvailable($type, $name)) {
            if ($type === 'chunk') {
                $content = $modx->parseChunk($name, $_GET, '[+', '+]');
            } else if ($type === 'snippet') {
                $content = $modx->runSnippet($name, $_GET);
            }
        }

        $this->sendForward($content);
    }

    public static function sendForward($content = '')
    {
        global $modx;

        $modx->forwards = $modx->forwards - 1;
        $modx->documentIdentifier = 1;
        $modx->documentMethod = 'id';
        $modx->documentObject = $modx->getDocumentObject($modx->documentMethod, $modx->documentIdentifier, 'prepareResponse');
        $modx->documentObject['content'] = $content;
        $modx->documentName = &$modx->documentObject['pagetitle'];

        if ($modx->documentObject['donthit'] == 1) {
            $modx->config['track_visitors'] = 0;
        }

        if ($modx->documentObject['deleted'] == 1) {
            $modx->sendErrorPage();
        } else if ($modx->documentObject['published'] == 0) {
            $modx->_sendErrorForUnpubPage();
        } else if ($modx->documentObject['type'] == 'reference') {
            $modx->_sendRedirectForRefPage($modx->documentObject['content']);
        }

        //if (!$modx->documentObject['template']) {
        $templateCode = '[*content*]';
        //} else {
        //    $templateCode = $modx->_getTemplateCodeFromDB($modx->documentObject['template']);
        //}

        if (substr($templateCode, 0, 8) === '@INCLUDE') {
            $templateCode = $modx->atBindInclude($templateCode);
        }

        $modx->documentContent = &$templateCode;
        //$modx->invokeEvent('OnLoadWebDocument');
        $modx->documentContent = $modx->parseDocumentSource($templateCode);
        $modx->documentGenerated = 1;

        $modx->outputContent();

        die();
    }

    protected function checkAvailable($type, $name)
    {
        if (isset($this->config['filter'])) {
            if ($this->config['filter'] === 'blacklist') {
                if (isset($this->config['blacklist'])) {
                    if (is_array($this->config['blacklist'])) {
                        if (!empty($this->config['blacklist'])) {
                            if (in_array("{$type}:{$name}", $this->config['blacklist'])) {
                                return false;
                            }
                        }
                    }
                }
            } else if ($this->config['filter'] === 'whitelist') {
                if (!isset($this->config['whitelist'])) {
                    return false;
                }

                if (!is_array($this->config['whitelist'])) {
                    return false;
                }

                if (empty($this->config['whitelist'])) {
                    return false;
                }

                if (!in_array("{$type}:{$name}", $this->config['whitelist'])) {
                    return false;
                }
            }
        }

        return true;
    }
}
