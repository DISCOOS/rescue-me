<?php
/**
 * File containing: Row service class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 6. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Service;

use ReflectionMethod;
use RescueMe\Admin\Core\CallableResolver;
use RescueMe\Properties;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Row service class
 * @package RescueMe\Admin\Service
 */
class RowService extends CallableResolver {

    /**
     * Template service
     * @var TemplateService
     */
    private $service;

    /**
     * Column data
     * @var array|callable
     */
    private $columns = array();

    /**
     * Row data
     * @var array|callable
     */
    private $rows = array();

    /**
     * Array of ReflectionMethod of callable
     * @var ReflectionMethod
     */
    protected $methods = array();


    /**
     * Constructor
     * @param TemplateService $service
     */
    function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * Connect service with data source
     * @param array|callable $rows Row data
     * @param array|callable $columns Column data
     */
    public function connect($rows, $columns) {
        // Store data
        $this->rows = $rows;
        $this->columns = $columns;

        // Perform reflection only once
        $this->methods['rows'] = $this->getMethod($rows);
        $this->methods['columns'] = $this->getMethod($columns);
    }


    /**
     * Render request into rows as html and paginator options
     * @param Application $app Silex application
     * @param Request $request Request instance
     * @param boolean|mixed|$user Current user
     * @return array array('rows' => $rows, 'options' => $options)
     */
    public function paginate(Application $app, Request $request, $user) {

        $rows = $this->rows;

        // Calculate paging information
        $page = $request->query->get('page', 1);
        $max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user->id);
        $start = $max * ($page - 1);

        // Set rows query parameters in row context
        $context = array(
               'name' => $request->query->get('name', false),
               'filter' => $request->query->get('filter', ''),
               'start' => $start,
               'max' => $max
            );

        // Lazy rows creation?
        if ($this->methods['rows']) {
            $arguments = $this->getArguments($this->methods['rows'], $app, $request, $user, $context);
            $rows = call_user_func_array($rows, $arguments);
            if(!$rows) {
                $rows = array();
            }
        }

        $columns = $this->columns;

        // Lazy columns creation?
        if ($this->methods['columns']) {
            $arguments = $this->getArguments($this->methods['columns'], $app, $request, $user, $context);
            $columns = call_user_func_array($columns, $arguments);
        }

        // Finalize context
        $context = array_merge($context, array(
                'rows' => $rows,
                'columns' => $columns
            ));

        // Calculate maximum number of pages
        $pages = max(1,ceil(count($rows)/$max));

        // Finished
        return array(
            'html' => $this->service->render($app, 'table.row.twig', $context),
            'options' => create_paginator(1, $pages, $user->id)
        );

    }
}