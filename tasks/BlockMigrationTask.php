<?php

namespace Dynamic\Elements\Migrator;

use DNADesign\Elemental\Models\BaseElement;
use Dynamic\BaseObject\Model\BaseElementObject;
use Dynamic\Elements\Accordion\Elements\ElementAccordion;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\Debug;
use SilverStripe\Subsites\Model\Subsite;

class BlockMigrationTask extends BuildTask
{
    /**
     * @var string
     */
    protected $title = 'Update Block Namespacing';

    /**
     * @var string
     */
    protected $description = 'Migration task - update depreciated namespaces for Dynamic Elemental blocks';

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @param $request
     */
    public function run($request)
    {
        if (class_exists(Subsite::class)) {
            // disable the subsite filter because it returns null otherwise
            $initialSubsiteFilter = Subsite::$disable_subsite_filter;
            Subsite::$disable_subsite_filter = true;
        }

        $this->updateNames();
        $this->updateObjectNames();

        if (class_exists(Subsite::class)) {
            // reset the subsite filter to what it was
            Subsite::$disable_subsite_filter = $initialSubsiteFilter;
        }
    }

    private $mapping = [
        "Dynamic\Elements\Elements\ElementAccordion" => "Dynamic\Elements\Accordion\Elements\ElementAccordion",
        "Dynamic\Elements\Elements\ElementBlogPosts" => "Dynamic\Elements\Blog\Elements\ElementBlogPosts",
        "Dynamic\Elements\Elements\ElementCountDown" => "Dynamic\Elements\CountDown\Elements\ElementCountDown",
        "Dynamic\Elements\Elements\ElementCustomerService" => "Dynamic\Elements\CustomerService\Elements\ElementCustomerService",
        "Dynamic\Elements\Elements\ElementEmbeddedCode" => "Dynamic\Elements\Embedded\Elements\ElementEmbeddedCode",
        "Dynamic\Elements\Elements\ElementFeatures" => "Dynamic\Elements\Features\Elements\ElementFeatures",
        "Dynamic\Elements\Elements\ElementSlideshow" => "Dynamic\Elements\Flexslider\Elements\ElementSlideshow",
        "Dynamic\ElementalFlexslider\Elements\ElementSlideshow" => "Dynamic\Elements\Flexslider\Elements\ElementSlideshow",
        "Dynamic\Elements\Elements\ElementPhotoGallery" => "Dynamic\Elements\Gallery\Elements\ElementPhotoGallery",
        "Dynamic\Elements\Elements\ElementImage" => "Dynamic\Elements\Image\Elements\ElementImage",
        "Dynamic\Elements\Elements\ElementOembed" => "Dynamic\Elements\Oembed\Elements\ElementOembed",
        "Dynamic\Elements\Elements\ElementPromos" => "Dynamic\Elements\Promos\Elements\ElementPromos",
        "Dynamic\Elements\Elements\ElementSectionNavigation" => "Dynamic\Elements\Section\Elements\ElementSectionNavigation",
        "Dynamic\Elements\Elements\ElementSponsor" => "Dynamic\Elements\Sponsors\Elements\ElementSponsor",
        "Dynamic\Elements\Elements\ElementTestimonials" => "Dynamic\Elements\Testimonials\Elements\ElementTestimonials",
    ];

    private $objectMapping = [
        "Dynamic\Elements\Model\AccordionPanel" => "Dynamic\Elements\Accordion\Model\AccordionPanel",
        "Dynamic\Elements\Model\BaseElementObject" => "Dynamic\BaseObject\Model\BaseElementObject",
        "Dynamic\Elements\Model\FeatureObject" => "Dynamic\Elements\Features\Model\FeatureObject",
        "Dynamic\Elements\Model\GalleryImage" => "Dynamic\Elements\Gallery\Model\GalleryImage",
        "Dynamic\Elements\Model\PromoObject" => "Dynamic\Elements\Promos\Model\PromoObject",
        "Dynamic\Elements\Model\Sponsor" => "Dynamic\Elements\Sponsors\Model\Sponsor",
        "Dynamic\Elements\Model\Testimonial" => "Dynamic\Elements\Testimonial\Model\Testimonial",

    ];

    /**
     * mark all ProductDetail records as ShowInMenus = 0.
     */
    public function updateNames()
    {
        $ct = 0;
        $blocks = BaseElement::get();
        foreach ($this->mapping as $key => $value) {
            $records = $blocks->filter("ClassName", $key);
            //Debug::show($records->count());
            foreach ($records as $record) {
                $record->ClassName = $value;
                $record->writeToStage('Stage');
                if ($record->isPublished()) {
                    $record->publish('Stage', 'Live');
                }
                //Debug::show($record->ClassName);
                static::write_message($record->Title . " updated");
                $ct++;
            }
        }

        static::write_message("<h4>{$ct} blocks updated.</h4>");
    }

    /**
     * mark all ProductDetail records as ShowInMenus = 0.
     */
    public function updateObjectNames()
    {
        $ct = 0;
        $blocks = BaseElementObject::get();
        foreach ($this->objectMapping as $key => $value) {
            $records = $blocks->filter("ClassName", $key);
            //Debug::show($records->count());
            foreach ($records as $record) {
                $record->ClassName = $value;
                $record->writeToStage('Stage');
                if ($record->isPublished()) {
                    $record->publish('Stage', 'Live');
                }
                static::write_message($record->Name . " updated");
                $ct++;
            }
        }

        static::write_message("<h4>{$ct} block objects updated.</h4>");
    }

    /**
     * @param $message
     */
    protected static function write_message($message)
    {
        if (Director::is_cli()) {
            echo "{$message}\n";
        } else {
            echo "{$message}<br><br>";
        }
    }
}