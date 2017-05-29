<?php

namespace Framework\CustomPosts;

class CptForms
{
    private function getFieldValue($field)
    {
        global $post;

        $current = get_post_meta($post->ID, $field['id'], true);

        if ($current) {
            return $current;
        } elseif (isset($field['default_value'])) {
            return $field['default_value'];
        }

        return '';
    }

    public function getFieldType($field)
    {
        if (isset($field['input_type'])) {
            return $field['input_type'];
        }

        return 'text';
    }

    public function createTextField($field)
    {
        $html = '<input type="' . $this->getFieldType($field) . '"';
        $html .= 'id="' . $field['id'] . '"';
        $html .= 'name="' . $field['id'] . '"';
        $html .= 'value="' . esc_attr($this->getFieldValue($field)) . '"';
        $html .= 'class="widefat"';
        $html .= '/>';

        return $html;
    }

    public function createTextArea($field)
    {
        $html = '<textarea cols="10" rows="3" class="widefat" id="' . $field['id'] . '" name="' . $field['id'] . '">';
        $html .= $this->getFieldValue($field);
        $html .= '</textarea>';

        return $html;
    }

    public function createSelectField($field)
    {
        return;
    }

    public function createImageField($field)
    {
        return;
    }

    public function createCheckboxField($field)
    {
        return;
    }

    public function createRadioButtons($field)
    {
        return;
    }
}
