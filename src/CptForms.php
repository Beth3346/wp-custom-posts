<?php
namespace WpCustomPosts;

class CptForms
{
    private function deslugify($str)
    {
        return ucwords(str_replace('_', ' ', $str));
    }

    private function getFieldValue($field)
    {
        global $post;

        $current = get_post_meta($post->ID, $field['id'], true);

        if ($current) {
            return $current;
        } else if (isset($field['default_value'])) {
            return $field['default_value'];
        }

        return '';
    }

    private function setFieldLabel(array $field)
    {
        return isset($field['label']) ? $field['label'] :  $this->deslugify($field['id']);
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

    private function getSelectOptions(array $options, $value = null)
    {
        $html = '';

        foreach ($options as $option) {
            // if option is the current value
            if ($value == $option) {
                $html .= '<option selected value="' . $option . '">' . $option . '</option>';
            } else {
                $html .= '<option value="' . $option . '">' . $option . '</option>';
            }
        }

        return $html;
    }

    public function createSelectField(array $field)
    {
        $id = $field['id'];
        $label = $this->setFieldLabel($field);
        $value = $this->getFieldValue($field);

        $html = '<select class="widefat" name="' . $id . '" id="' . $id . '">';
        $html .= '<option value="">Select ' . $label . '</option>';
            $html .= $this->getSelectOptions($field['options'], $value);
        $html .= '</select>';

        return $html;
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
