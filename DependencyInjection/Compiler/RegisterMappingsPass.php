<?php

/*
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pecserke\Component\UniversalMappings\DependencyInjection\Compiler;

use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass as AbstractRegisterMappingsPass;

/**
 * Forward compatibility class in case the bundle is used with
 * the doctrine bundles that do not provide the
 * register mappings compiler pass yet.
 *
 * The compiler pass is meant to register the mappings with the metadata
 * chain driver corresponding to one of the object managers.
 *
 * @author Tomas Pecserke <tomas@pecserke.eu>
 */
class RegisterMappingsPass extends AbstractRegisterMappingsPass
{
}
