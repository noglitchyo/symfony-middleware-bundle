<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Entity\HandlerConfiguration;

class Filter
{
    /**
     * @var string|null
     */
    private $routeName;

    /**
     * @var string|null
     */
    private $routePath;

    /**
     * @var string|null
     */
    private $controller;

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName = null): self
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getRoutePath(): ?string
    {
        return $this->routePath;
    }

    public function setRoutePath(string $routePath = null): self
    {
        $this->routePath = $routePath;

        return $this;
    }

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function setController(string $controller = null): self
    {
        $this->controller = $controller;

        return $this;
    }
}