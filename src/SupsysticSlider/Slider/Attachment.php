<?php

/**
 * Attachments handler.
 */
class SupsysticSlider_Slider_Attachment
{

    /**
     * Returns attachment image with requested sizes.
     * If it is not possible to get requested size method returns placeholder.
     *
     * @param int $attachmentId Attachment Id.
     * @param int $width Requested image width.
     * @param int $height Requested image height.
     * @return string
     */
    public function getSize($attachmentId, $width, $height = null)
    {
        $attachment = $this->getMetadata($attachmentId);

        if (!$attachment) {
            if (!$height) {
                $height = $width;
            }

            return $this->getPlaceholderUrl($width, $height);
        }

        if ($url = $this->getDefaultSizeUrl($attachment, $width, $height)) {
            return $url;
        }

        if ($url = $this->getCroppedSizeUrl($attachment, $width, $height)) {
            return $url;
        }

        if ($url = $this->crop($attachment, $width, $height)) {
            return $url;
        }

        if (!isset($attachment['sizes']) || !isset($attachment['sizes']['full'])) {
            return $this->getPlaceholderUrl($width, $height);
        }

        return $attachment['sizes']['full']['url'];
    }

    /**
     * Returns attachment metadata by attachment id.
     *
     * @param int $attachmentId Attachment Id.
     * @return array
     */
    public function getMetadata($attachmentId)
    {
        return wp_prepare_attachment_for_js($attachmentId);
    }

    /**
     * Returns full path to the attachment or NULL on failure.
     *
     * @param array $attachment Attachment metadata.
     * @return null|string
     */
    public function getFilePath($attachment)
    {
        if (!is_array($attachment) || !isset($attachment['url'])) {
            return null;
        }

        $url = $attachment['url'];
        $basepath = untrailingslashit(ABSPATH);

        return  $basepath . str_replace(get_bloginfo('wpurl'), '', $url);
    }

    /**
     * Returns url to the requested size or NULL if this size does not exists.
     *
     * @param array $attachment Attachment metadata.
     * @param int $width Requested width.
     * @param int $height Requested height.
     * @return null|string
     */
    protected function getDefaultSizeUrl($attachment, $width, $height)
    {
        if (!$height) {
            return null;
        }

        foreach ($attachment['sizes'] as $size) {
            if ($size['width'] === $width && $size['height'] === $height) {
                return $size['url'];
            }
        }

        return null;
    }

    /**
     * Crops the attachment image and return path to the cropped image.
     * If crop fails returns NULL.
     *
     * @param array $attachment Attachment metadata.
     * @param int $width Image width.
     * @param int $height Image height.
     * @return string|null
     */
    protected function crop($attachment, $width, $height = null)
    {
        $filepath = $this->getFilePath($attachment);
        $editor   = $this->getEditor($filepath);

        if (!$editor) {
            return null;
        }

        if (is_wp_error($editor->resize($width, $height, true))) {
            return null;
        }

        if (is_wp_error($data = $editor->save())) {
            return null;
        }

        unset($editor);

        return str_replace(ABSPATH, get_bloginfo('wpurl') . '/', $data['path']);
    }

    /**
     * Returns WP_Image_Editor or NULL on failure.
     *
     * @param string $filepath Path to file.
     * @return WP_Image_Editor
     */
    protected function getEditor($filepath)
    {
        $editor = wp_get_image_editor($filepath);

        if (is_wp_error($editor)) {
            return null;
        }

        return $editor;
    }

    /**
     * Returns URL to the images if WordPress has already cropped
     * and resized image.
     * If uploads directory doesn't contain requested file - returns NULL.
     *
     * @param array $attachment Attachment metadata.
     * @param int $width Image width.
     * @param int $height Image height.
     * @return string|null
     */
    protected function getCroppedSizeUrl($attachment, $width, $height)
    {
        if (!is_array($attachment) || (!$width || !$height)) {
            return null;
        }

        $filepath  = $this->getFilePath($attachment);
        $filename  = pathinfo($filepath, PATHINFO_FILENAME);
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);

        // Will be something file: filename-300x300.jpg
        $filename = $filename . '-' . $width . 'x' . $height . '.' . $extension;

        if (is_file($file = dirname($filepath) . '/' . $filename)) {
            return str_replace(ABSPATH, get_bloginfo('wpurl') . '/', $file);
        }

        return null;
    }

    /**
     * Returns URL to the placeholder with specified width, height and text.
     *
     * @param int    $width  Image width.
     * @param int    $height Image height.
     * @param string $text   Image text.
     * @return string
     */
    protected function getPlaceholderUrl($width, $height, $text = null)
    {
        $text = $text ? $text : 'Failed+to+load+image.';

        return sprintf(
            'http://placehold.it/%sx%s&text=%s',
            $width,
            $height,
            $text
        );
    }
} 