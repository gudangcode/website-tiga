<?php

namespace Acelle\Library\Automation;

use Carbon\Carbon;

abstract class Action
{
    protected $id;
    protected $title;
    protected $type;
    protected $latest = false;
    protected $child;
    protected $options;
    protected $last_executed = null;
    protected $evaluationResult = null;

    // parent object
    protected $autoTrigger;

    public function __construct($params = [])
    {
        $this->id = $params['id'];
        $this->title = $params['title'];
        $this->type = $params['type'];
        $this->child = array_key_exists('child', $params) ? $params['child'] : null;
        $this->options = array_key_exists('options', $params) ? $params['options'] : [];
        $this->latest = array_key_exists('latest', $params) ? $params['latest'] : null;
        $this->last_executed = array_key_exists('last_executed', $params) ? $params['last_executed'] : null;
        $this->evaluationResult = array_key_exists('evaluationResult', $params) ? $params['evaluationResult'] : null;
    }

    public function setAutoTrigger($autoTrigger)
    {
        $this->autoTrigger = $autoTrigger;
    }

    public function toJson()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'type' => $this->type,
            'child' => $this->child,
            'latest' => $this->isLatest(),
            'options' => $this->getOptions(),
            'last_executed' => $this->getLastExecuted(),
            'evaluationResult' => $this->evaluationResult,
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getLastExecuted()
    {
        return $this->last_executed;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($key)
    {
        return $this->options[$key];
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function getNextActionId()
    {
        return $this->child;
    }

    public function getEvaluationResult()
    {
        return $this->evaluationResult;
    }

    public function isLatest()
    {
        return $this->latest;
    }

    public function markAsLatest($latest = true)
    {
        $this->latest = $latest;
    }

    public function recordLastExecutedTime()
    {
        $this->last_executed = Carbon::now()->timestamp;
    }

    public function hasChild($e)
    {
        if (is_null($this->child)) {
            return false;
        }

        return $e->getId() == $this->child;
    }

    public function getParent()
    {
        $parent = null;
        $this->autoTrigger->getActions(function ($action) use (&$parent) {
            if ($action->hasChild($this)) {
                $parent = $action;
            }
        });

        return $parent;
    }
}
