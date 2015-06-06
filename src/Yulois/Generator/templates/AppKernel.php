[?php
/**
 * This file is part of the yulois Framework.
 *
 * (c) Jorge Gaitan <jorge@yulois.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Yulois\Kernel;

Class ##CLASS## ##EXTENDS##
{
    public static function registryBundles()
    {
        return array(
        <?php foreach($data['bundles'] as $namespace):?>
        '<?php echo $namespace; ?>\\',
        <?php endforeach; ?>
        );
    }
}