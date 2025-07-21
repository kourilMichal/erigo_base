<?php 

/**
 * @author		STUDIO ERIGO, s.r.o.
 * @copyright	Copyright (c) 2023 STUDIO ERIGO, s.r.o.
 */

namespace Erigo\ErigoBase\Event\Controller;

use TYPO3Fluid\Fluid\View\ViewInterface;

class InitializeViewEvent
{
    public function __construct(
        protected ViewInterface $view,
        protected array $settings,
        protected string $controllerClassName,
    ) {}
    
    public function getView(): ViewInterface
    {
        return $this->view;
    }
    
    public function getSettings(): array
    {
        return $this->settings;
    }
    
    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
    
    public function getControllerClassName(): string
    {
        return $this->controllerClassName;
    }
}