<?php

namespace Tests\Unit\Services;

use App\Services\AttributionModels\LastClick;
use App\Services\AttributionModels\FirstClick;
use App\Services\AttributionModels\Linear;
use App\Services\AttributionModels\TimeDecay;
use App\Services\AttributionModels\PositionBased;
use Tests\TestCase;

class AttributionModelsTest extends TestCase
{
    public function test_last_click_gives_all_weight_to_last_touch()
    {
        $model = new LastClick();
        $weights = $model->getTouchWeights(3);
        $this->assertEquals([0, 0, 1], $weights);
    }

    public function test_first_click_gives_all_weight_to_first_touch()
    {
        $model = new FirstClick();
        $weights = $model->getTouchWeights(3);
        $this->assertEquals([1, 0, 0], $weights);
    }

    public function test_linear_gives_equal_weight()
    {
        $model = new Linear();
        $weights = $model->getTouchWeights(4);
        $this->assertCount(4, $weights);
        foreach ($weights as $w) {
            $this->assertEquals(0.25, $w);
        }
    }

    public function test_time_decay_increases_with_later_touches()
    {
        $model = new TimeDecay();
        $weights = $model->getTouchWeights(3);
        $this->assertCount(3, $weights);
        $this->assertGreaterThan($weights[1], $weights[2]);
    }

    public function test_position_based_gives_40_20_40()
    {
        $model = new PositionBased();
        $weights = $model->getTouchWeights(5);
        $this->assertEquals(0.4, $weights[0]);
        $this->assertEquals(0.4, $weights[4]);
        $this->assertEquals(0.2 / 3, $weights[1]);
    }

    public function test_position_based_single_touch()
    {
        $model = new PositionBased();
        $weights = $model->getTouchWeights(1);
        $this->assertEquals([1], $weights);
    }

    public function test_empty_weights_for_zero_touches()
    {
        $last = new LastClick();
        $this->assertEquals([], $last->getTouchWeights(0));
    }
}
