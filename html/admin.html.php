<?php if (!defined('ABSPATH')) { exit; } ?>
<main class="ucpl-body">
  <nav class="ucpl-header"
       role="navigation"
       data-bind="visible: modalIsNotActive">
    <div class="ucpl-navbar">
      <a class="ucpl-cp-logo" href="https://convertplayer.com" target="_blank">
        <i class="icon-ucpl-logo"></i>&nbsp;ConvertPlayer
      </a>
    </div>
  </nav>
  <div class="ucpl-content">
    <!-- ko template: { name: 'ucpl-convertplayer-template' } -->
      <div class="ucpl-spinner"></div>
    <!-- /ko -->
  </div>
</main>
<script src="https://cpem.io/app.js"></script>
