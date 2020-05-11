<?php

class BasicCard extends View
{
    private $border_class;
    private $href;
    private $title;
    private $description;
    private $icon_class;

    public function setBorderClass(string $class_name): BasicCard
    {
        $this->border_class = $class_name;
        return $this;
    }
    public function setHref(string $href): BasicCard
    {
        $this->href = $href;
        return $this;
    }
    public function setTitle(string $title): BasicCard
    {
        $this->title = $title;
        return $this;
    }
    public function setDescription(string $description): BasicCard
    {
        $this->description = $description;
        return $this;
    }
    public function setIconClass(string $class_name): BasicCard
    {
        $this->icon_class = $class_name;
        return $this;
    }

    public function render()
    { ?>
        <div class="<?php echo $this->renderClasses(); ?>">
            <div class="card <?php echo $this->border_class; ?> shadow h-100 py-2">
                <a href="<?php echo $this->href; ?>" class="text-decoration-none">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-sm font-weight-bold text-primary text-uppercase mb-1"><?php echo $this->title; ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $this->description; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-2x text-gray-800 <?php echo $this->icon_class; ?>"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
<?php }
}
