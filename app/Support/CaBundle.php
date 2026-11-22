<?php

namespace App\Support;

use Composer\CaBundle\CaBundle as ComposerCaBundle;

/**
 * One certificate bundle for every outgoing HTTP request, so HTTPS works the
 * same whatever the machine's php.ini says (fresh Windows PHP installs often
 * have no CA list configured).
 *
 * It combines the system/Mozilla CA list (composer/ca-bundle) with extra
 * intermediate certificates in resources/certs, for government sites that send
 * an incomplete chain (browsers repair this silently, PHP does not).
 */
final class CaBundle
{
    public static function path(): string
    {
        $base = ComposerCaBundle::getSystemCaRootBundlePath();
        $extras = glob(resource_path('certs/*.pem')) ?: [];

        if ($extras === []) {
            return $base;
        }

        $bundle = storage_path('app/ca-bundle.pem');
        $newest = max(array_map('filemtime', [$base, ...$extras]));

        if (! is_file($bundle) || filemtime($bundle) < $newest) {
            $pem = file_get_contents($base);
            foreach ($extras as $file) {
                $pem .= "\n".file_get_contents($file);
            }
            file_put_contents($bundle, $pem, LOCK_EX);
        }

        return $bundle;
    }
}
