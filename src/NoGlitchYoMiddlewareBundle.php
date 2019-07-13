<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle;

use NoGlitchYo\MiddlewareBundle\DependencyInjection\NoGlitchYoMiddlewareExtension;
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
}
