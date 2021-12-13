<style type="text/css" media="screen">
    <?php include_once dirname(__DIR__, 1) . '/css/app.css'; ?>
</style>
<div>
    <div>
        <?php echo $this->stackTrace['code']; ?>
        <?php echo strtoupper($this->stackTrace['type']); ?>
        <a href="https://stackoverflow.com/search?q=[php]+<?php echo urlencode($this->stackTrace['message']); ?>">&#9906;</a>
    </div>
    <div>
        <?php echo $this->mode; ?>
    </div>
    <div>
        <?php echo $this->stackTrace['message']; ?>
    </div>
    <div>
        <div>
            <?php echo $this->stackTrace['file']; ?>
        </div>
        <code>
            <?php echo $this->preview; ?>
        </code>
    </div>
    <div>
        <p>Backtrace:</p>
        <ul>
            <?php foreach ($this->stackTrace['backtrace'] as $key => $value) : ?>
                <li><?php echo $key . ' ' . $value; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
