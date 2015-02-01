<?php

/**
 * In-plugin event dispatcher.
 * Dispatchers plugin-specific events.
 */
class Rsc_Dispatcher 
{

    /**
     * @var Rsc_Environment;
     */
    protected $environment;

    /**
     * Constructor.
     * @param Rsc_Environment $environment Plugin environment.
     */
    public function __construct(Rsc_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Adds action.
     *
     * @see add_action()
     * @see http://codex.wordpress.org/Function_Reference/add_action
     * @param string   $action   The name of the action to which $function is hooked.
     * @param callable $function The name of the function you wish to be hooked.
     * @param int      $priority Used to specify the order in which the functions associated with a particular action are executed.
     * @param int      $args     The number of arguments the hooked function accepts.
     * @throws InvalidArgumentException
     * @return Rsc_Dispatcher
     */
    public function on($action, $function, $priority = 10, $args = 1)
    {
        if (!is_callable($function)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 2 passed to Rsc_Dispatcher::on() must be a callable, %s given.',
                gettype($function)
            ));
        }

        add_action($this->appendPrefix($action), $function, $priority, $args);

        return $this;
    }

    /**
     * Triggers the specified action.
     *
     * @param string $action Action name
     * @param array $parameters An array of the parameters.
     */
    public function dispatch($action, array $parameters = array())
    {
        do_action_ref_array(
            $this->appendPrefix($action),
            $parameters
        );
    }

    /**
     * Applies filters.
     *
     * @param string $action Action name
     * @param array $parameters An array of the parameters.
     * @return mixed
     */
    public function apply($action, array $parameters = array())
    {
        return apply_filters_ref_array(
            $this->appendPrefix($action),
            $parameters
        );
    }

    /**
     * Returns prefix for the hooks.
     * @return string
     */
    protected function getHooksPrefix()
    {
        $config = $this->environment->getConfig();

        return $config->get(
            'hooks_prefix',
            $this->environment->getPluginName()
        );
    }

    /**
     * Appends the prefix to the action name.
     * @param string $action Action name.
     * @return string
     */
    protected function appendPrefix($action)
    {
        return $this->getHooksPrefix() . $action;
    }
} 