<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use NoGlitchYo\MiddlewareBundle\Entity\HandlerConfigurationInterface;
use NoGlitchYo\MiddlewareBundle\Middleware\Stack\MiddlewareStackInterface;

class MiddlewareStackConfigurator
{
    /**
     * @var iterable|HandlerConfigurationInterface[]
     */
    private $handlerConfigurations;

    public function __construct(iterable $handlers)
    {
        $this->handlerConfigurations = $handlers;
    }

    public function __invoke(MiddlewareStackInterface $middlewareStack)
    {
        foreach ($this->handlerConfigurations as $handlerConfiguration) {
            $middlewareCollection = $handlerConfiguration->getCollection();

            if (!empty($handlerConfiguration->getConditions())) {
                $conditions = $handlerConfiguration->getConditions();
                foreach ($conditions as $condition) {
                    $middlewareStack->add($middlewareCollection, $condition);
                }
            } else {
                $middlewareStack->add($middlewareCollection);
            }
        }
    }
}
