<?php

namespace WpPluginium\Framework\Concern;

trait PluginPath
{
    public function getBasePathAttribute()
    {
        return untrailingslashit($this->pluginPath);
    }

    public function getAppPathAttribute()
    {
        return $this->pluginPath . 'app';
    }

    public function getBootstrapPathAttribute()
    {
        return $this->pluginPath . 'bootstrap';
    }

    public function getConfigPathAttribute()
    {
        return $this->pluginPath . 'config';
    }

    public function getPublicPathAttribute()
    {
        return $this->pluginPath . 'public';
    }

    public function getResourcePathAttribute()
    {
        return $this->pluginPath . 'resource';
    }

    public function getStoragePathAttribute()
    {
        return $this->pluginPath . 'storage';
    }

    public function getVendorPathAttribute()
    {
        return $this->pluginPath . 'vendor';
    }

    public function getWpPropertyPathAttribute()
    {
        return $this->pluginPath . 'wp-property';
    }
}
