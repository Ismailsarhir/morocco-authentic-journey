<?php

namespace Scripts\Models;

		
Class Taxonomy {
	private $title;
	private $slug;
	private $old_path;
	private $type;
	private $tax_args;

	/**
	 * [__construct]
	 * @param string    $title         [Titre de la taxonomy] Obligatoire
	 * @param string    $slug          [Slug de la taxonomy] Optionnel
	 * @param array     $tax_args      [Les args de la taxonomy] Obligatoire
	 *                  Exemple de tax_args
	 *                  [
	 *                  	'old_parent_cat_id' => 15238, // optionnel
	 *                  	'term_metas' => [
	 *                  		'old_id' => 15236, // obligatoire
	 *                  	],
	 *                  	'wpseo_metas' => [] // optionnel
	 *                  ]
	 *
	 * @param string    $type          [Type de la taxonomy, default = category] Optionnel
	 * @param array     $tax_relations [Les taxonomies attachés] Optionnel
	 */
	function __construct(string $title, string $slug='', array $tax_args, string $type='category', string $old_path = '')
	{
		$this->title = $title;
		$this->slug = $slug;
		$this->tax_args = $tax_args;
		$this->old_path = $old_path;
		$this->type = !empty($type) ? $type : 'category';
	}


	/* Getters */

	function get_title(): string
	{
		return $this->title;
	}

	function get_slug(): ?string
	{
		return $this->slug;
	}

	function get_type(): string
	{
		return $this->type;
	}

	function get_old_path(): string
	{
		return $this->old_path;
	}

	function get_tax_args(): array
	{	
		//echo 'get_tax_args :' ;
		//print_r($this->tax_args) ;

		return $this->tax_args;
	}

	function get_old_id(): string
	{
		return $this->tax_args['term_metas'][$this->type.'_old_id'] ?? '';
	}

	function is_valid(): bool
	{
		if(empty($this->title) || empty($this->tax_args['term_metas'][$this->type.'_old_id'])) {
			return false;
		}
		return true;
	}
}