<?php
namespace WpCustomPosts;

class Cpt
{
    public function deslugify($str)
    {
        return ucwords(str_replace('_', ' ', $str));
    }

    public function slugify($str)
    {
        return str_replace('_', '-', $str);
    }

    public function getDefaultSettings($singular_name, $plural_name)
    {
        return [
            $singular_name . '_root'      => $this->slugify($plural_name),
            $singular_name . '_base'      => $this->slugify($plural_name),
            $singular_name . '_item_base' => '%' . $this->slugify($singular_name) . '%'
        ];
    }

    public function getSingularName($cpt)
    {
        if (isset($cpt['singular_name'])) {
            return $cpt['singular_name'];
        }

        return;
    }

    public function getPluralName($cpt)
    {
        return isset($cpt['plural_name']) ? $cpt['plural_name'] : $this->getSingularName($cpt) . 's';
    }

    private function getLabels($cpt)
    {
        $singular_name = $this->getSingularName($cpt);
        $plural_name = $this->getPluralName($cpt);
        $text_domain = 'elr-' . $this->slugify($singular_name);

        return [
            'name'               => __($this->deslugify($plural_name), $text_domain),
            'singular_name'      => __($this->deslugify($singular_name), $text_domain),
            'menu_name'          => __($this->deslugify($plural_name), $text_domain),
            'name_admin_bar'     => __($this->deslugify($singular_name), $text_domain),
            'add_new'            => __('Add New', $text_domain),
            'add_new_item'       => __('Add New ' . $this->deslugify($singular_name), $text_domain),
            'edit_item'          => __('Edit ' . $this->deslugify($singular_name), $text_domain),
            'new_item'           => __('New ' . $this->deslugify($singular_name), $text_domain),
            'view_item'          => __('View ' . $this->deslugify($singular_name), $text_domain),
            'search_items'       => __('Search ' . $this->deslugify($plural_name), $text_domain),
            'not_found'          => __('No ' . str_replace('_', ' ', $plural_name) . ' found', $text_domain),
            'not_found_in_trash' => __('No ' . str_replace('_', ' ', $plural_name) . ' found in trash', $text_domain),
            'all_items'          => __($this->deslugify($plural_name), $text_domain),
            // Custom labels b/c WordPress doesn't have anything to handle this.
            'archive_title'      => __($this->deslugify($plural_name), $text_domain),
        ];
    }

    private function getCapabilities($cpt)
    {
        $singular_name = $this->getSingularName($cpt);
        $plural_name = $this->getPluralName($cpt);

        return [
                // meta caps (don't assign these to roles)
                'edit_posts'              => 'edit_' . $plural_name,
                'read_post'              => 'read_' . $singular_name,
                'delete_post'            => 'delete_' . $singular_name,
                // primitive/meta caps
                'create_posts'           => 'create_' . $plural_name,
                // primitive caps used outside of map_meta_cap()
                'edit_posts'             => 'edit_' . $plural_name,
                'read_private_posts'     => 'read',
                // primitive caps used inside of map_meta_cap()
                'read'                   => 'read',
                'edit_private_posts'     => 'edit_' . $plural_name,
                'edit_published_posts'   => 'edit_' . $plural_name
        ];
    }

    private function hasArchive($cpt)
    {
        $singular_name = $this->getSingularName($cpt);
        $plural_name = $this->getPluralName($cpt);
        $archive = isset($cpt['archive']) ? $cpt['archive'] : true;
        // Get the plugin settings.
        $settings = get_option('plugin_elr_' . $plural_name, $this->getDefaultSettings($singular_name, $plural_name));

        if ($archive == true) {
            return $settings[$singular_name . '_root'];
        } elseif ($archive == false) {
            return false;
        }

        return $archive;
    }

    private function getArgs($cpt)
    {
        $singular_name = $this->getSingularName($cpt);
        $supports = isset($cpt['supports']) ? $cpt['supports'] : ['title', 'editor', 'thumbnail', 'comments'];
        $taxonomies = isset($cpt['taxonomies']) ? $cpt['taxonomies'] : ['category', 'post_tags'];
        $hierarchical = isset($cpt['hierarchical']) ? $cpt['hierarchical'] : false;
        $description = isset($cpt['description']) ? $cpt['description'] : '';

        return [
            'description'         => $description,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 11,
            'can_export'          => true,
            'delete_with_user'    => false,
            'hierarchical'        => $hierarchical,
            'taxonomies'          => $taxonomies,
            'has_archive'         => $this->hasArchive($cpt),
            'query_var'           => $singular_name,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,

            // Only 3 caps are needed: 'manage', 'create', and 'edit'.
            'capabilities' => $this->getCapabilities($cpt),
            // What features the post type supports.
            'supports' => $supports,
            // Labels used when displaying the posts.
            'labels' => $this->getLabels($cpt)
        ];
    }

    /**
     * Registers post type.
     */

    private function registerPostType($cpt)
    {
        register_post_type($this->getSingularName($cpt), $this->getArgs($cpt));
    }

    public function setRoles($cpt)
    {
        if (!$this->getSingularName($cpt)) {
            return;
        }

        // Get the administrator role.
        $role = get_role('administrator');

        // If the administrator role exists, add required capabilities for the plugin.
        if (!empty($role)) {
            $role->add_cap('manage_' . $this->getSingularName($cpt));
            $role->add_cap('create_' . $this->getPluralName($cpt));
            $role->add_cap('edit_' . $this->getPluralName($cpt));
        }
    }

    public function register($cpt)
    {
        // if there is no singular name don't attempt to register the post type
        if (!$this->getSingularName($cpt)) {
            return;
        }

        add_action('init', function () use ($cpt) {
                return $this->registerPostType($cpt);
        }, 12);
    }
}
