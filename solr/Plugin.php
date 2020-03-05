<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Plugin\Solr;

use Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Plugin extends BasePlugin
{
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('solr')
                    ->children()
                        ->arrayNode('envs')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('core')->end()
                                    ->scalarNode('prefix')->end()
                                    ->scalarNode('host')->defaultValue('localhost')->end()
                                    ->scalarNode('port')->defaultValue('8983')->end()
                                    ->scalarNode('ssh')->end()
                                    ->scalarNode('classpath')->defaultValue('/opt/solr/server/solr/mycores/')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->children()
                        ->arrayNode('entities')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Small helper for calling declared methods with arguments.
     *
     * @param Container $container
     * @param array|string $id
     * @param mixed ...$args
     * @return mixed
     */
    protected function call(Container $container, $id, ...$args)
    {
        $ret = $container->resolve($id);

        if (is_array($ret) && $ret[0] instanceof \Closure && is_bool($ret[1])) {
            return ($ret[1]) ? $ret[0]($container, ...$args) : $ret[0](...$args);
        }

        return null;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {

        $container->method(
            ['solr', 'get', 'ssh'],
            function (Container $c, $env) {                
                if (null !== ($ssh = $c->resolve(sprintf('solr.envs.%s.ssh', $env)))) {
                    return $ssh;                   
                } else {
                    return $c->resolve(sprintf('envs.%s.ssh', $env));
                }
            }
        );

        $container->method(
            ['solr', 'do', 'ssh'],
            function (Container $c, $env) {
                return 'ssh ' . $this->call($c, 'solr.get.ssh', $env);
            }
        );
    }
}
