<?php

namespace ProgA\SystemDependecies;

class Component {
    private $name;
    private $dependencies;
    private $dependents;
    private $installed;
    private $explicitInstall;

    function __construct($name, $dependencyList)
    {
        $this->name = $name;
        $this->setDependenciesList($dependencyList);
        $this->installed = false;
        $this->explicitInstall = false;
    }

    /**
     * @return mixed
     */
    public function getDependencies()
    {
        return $this->$dependencies;
    }

    /**
     * @return mixed
     */
    public function getDependents()
    {
        return $this->dependents;
    }

    /**
     * @param mixed $dependencies
     */
    public function setDependenciesList($dependencies)
    {
        foreach($dependencies as $dependency) {
            $component = ComponentManager::getInstance()->getComponent($dependency);
            $component->setDependent($this->getName());
        }
        $this->dependencies = $dependencies;
    }

    public function setDependent($dependent)
    {
        $this->dependents[$dependent] = $dependent;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function isInstalled()
    {
        return $this->installed;
    }

    public function install($explicit = false)
    {
        if($this->isInstalled()) {
            echo "  {$this->getName()} is already installed.\n";
            return;
        }
        /** @var Component $dependency */
        foreach($this->dependencies as $dependency) {
            /** @var Component $component */
            $component = ComponentManager::getInstance()->getComponent($dependency);
            if(!$component->isInstalled()) {
                $component->install();
            }
        }
        echo "  Installing {$this->getName()}\n";
        $this->installed = true;
        if($explicit) {
            $this->explicitInstall = true;
        }
    }

    public function remove()
    {
        if(!$this->isInstalled()) {
            echo "  {$this->getName()} is not  installed.\n";
            return;
        }
        if(!empty($this->dependents)) {
            /** @var Component $dependent */
            foreach($this->dependents as $dependent) {
                if(ComponentManager::getInstance()->getComponent($dependent)->isInstalled()) {
                    echo "  {$this->getName()} is still needed.\n";
                    return;
                }
            }
        }
        echo "  Removing {$this->getName()}\n";
        $this->installed = false;
        /** @var Component $dependency */
        foreach($this->dependencies as $dependency) {
            /** @var Component $component */
            $component = ComponentManager::getInstance()->getComponent($dependency);
            foreach($component->getDependents() as $dependent) {
                if(ComponentManager::getInstance()->getComponent($dependent)->isInstalled()) {
                    return;
                }
            }
            if(!$component->explicitInstall) {
                $component->remove();
            }
        }
    }

}