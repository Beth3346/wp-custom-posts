<?php
namespace WpCustomPosts;

class CustomTaxonomyBuilder
{
    /**
     * Adds default terms to taxonomy
     */

    private function taxonomyAddDefaultTerms($parent, $terms)
    {
        foreach ($terms as $term) {
            return $this->addDefaultTaxTerm($parent, $terms);
        }
    }

    private function addDefaultTaxTerm($parent, $term)
    {
        $parent_term = term_exists($parent, $parent);
        $parent_term_id = $parent_term['term_id'];

        if (!term_exists($term, $parent)) {
            wp_insert_term(
                $term,
                $parent,
                [
                    'slug' => $term,
                    'parent'=> $parent_term_id
                ]
            );
        }
    }

    private function register($tax, $cpt_singular, $cpt_plural)
    {
        add_action('init', function () use ($tax, $cpt_singular, $cpt_plural) {
            return $this->registerTaxonomy($tax, $cpt_singular, $cpt_plural);
        }, 12);

        return;
    }

    private function getSingularTaxName($tax)
    {
        $builder = new Cpt;
        return $builder->getSingularName($tax);
    }

    private function getPluralTaxName($tax)
    {
        $builder = new Cpt;
        return $builder->getPluralName($tax);
    }

    private function getLabels($tax)
    {
        $builder = new Cpt;
        $singular_name = $this->getSingularTaxName($tax);
        $plural_name = $this->getPluralTaxName($tax);
        $text_domain = 'elr-' . $builder->slugify($singular_name);

        return [
            'name'                       => __($builder->deslugify($plural_name), $text_domain),
            'singular_name'              => __($builder->deslugify($singular_name), $text_domain),
            'menu_name'                  => __($builder->deslugify($plural_name), $text_domain),
            'name_admin_bar'             => __($builder->deslugify($singular_name), $text_domain),
            'search_items'               => __('Search ' . $builder->deslugify($plural_name), $text_domain),
            'popular_items'              => __('Popular ' . $builder->deslugify($plural_name), $text_domain),
            'all_items'                  => __('All ' . $builder->deslugify($plural_name), $text_domain),
            'edit_item'                  => __('Edit ' . $builder->deslugify($singular_name), $text_domain),
            'view_item'                  => __('View ' . $builder->deslugify($singular_name), $text_domain),
            'update_item'                => __('Update ' . $builder->deslugify($singular_name), $text_domain),
            'add_new_item'               => __('Add New ' . $builder->deslugify($singular_name), $text_domain),
            'new_item_name'              => __('New ' . $builder->deslugify($singular_name) . ' Name', $text_domain),
            'add_or_remove_items'        => __('Add or remove ' . str_replace('_', ' ', $plural_name), $text_domain),
            'choose_from_most_used'      => __('Choose from the most used ' . str_replace('_', ' ', $plural_name), $text_domain),
            'separate_items_with_commas' => __('Separate ' . str_replace('_', ' ', $plural_name) . ' with commas', $text_domain)
        ];
    }

    private function getCapabilities($cpt_singular, $cpt_plural)
    {
        /* Only 2 caps are needed: 'manage' and 'edit'. */
        return [
            'manage_terms' => 'manage_' . $cpt_singular,
            'edit_terms'   => 'manage_' . $cpt_singular,
            'delete_terms' => 'manage_' . $cpt_singular,
            'assign_terms' => 'edit_' . $cpt_plural,
        ];
    }

    /**
     * Register taxonomies for the plugin.\
     */

    private function registerTaxonomy($tax, $cpt_singular, $cpt_plural)
    {
        $singular_name = $this->getSingularTaxName($tax);
        $plural_name = $this->getPluralTaxName($tax);
        $hierarchical = isset($tax['hierarchical']) ? $tax['hierarchical'] : true;
        $default_terms = isset($tax['default_terms']) ? $tax['default_terms'] : [];
        $builder = new Cpt;

        /* Set up the arguments for the priority taxonomy. */
        $args = [
            'public'            => true,
            'show_ui'           => true,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'hierarchical'      => $hierarchical,
            'query_var'         => $singular_name,
            'capabilities' => $this->getCapabilities($cpt_singular, $cpt_plural),
            'labels' => $this->getLabels($tax)
        ];

        // Register the taxonomy
        register_taxonomy($singular_name, [$cpt_singular], $args);

        // add default terms
        $this->taxonomyAddDefaultTerms($singular_name, $default_terms);
    }

    public function registerTaxonomies($cpt)
    {
        $builder = new CPT;
        $taxonomies = $cpt['custom_taxonomies'];

        foreach ($taxonomies as $tax) {
            $this->register($tax, $builder->getSingularName($cpt), $builder->getPluralName($cpt));
        }

        return;
    }
}
