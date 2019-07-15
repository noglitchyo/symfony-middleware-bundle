<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use NoGlitchYo\MiddlewareBundle\DependencyInjection\Compiler\AddCollectionPass;
use NoGlitchYo\MiddlewareBundle\DependencyInjection\NoGlitchYoMiddlewareExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NoGlitchYoMiddlewareBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new NoGlitchYoMiddlewareExtension();
        }
        return $this->extension;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddCollectionPass());
    }
}
