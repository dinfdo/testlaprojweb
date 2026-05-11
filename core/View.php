<?php
class View {
    public static function render(string $template, array $data = []): void {
        $__file = VIEW_PATH . '/' . $template . '.tpl';
        if (!file_exists($__file)) {
            throw new RuntimeException("View not found: $__file");
        }
        extract($data);
        require $__file;
    }
}
