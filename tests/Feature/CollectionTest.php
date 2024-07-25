<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    
    public function testCreateCollection(): void
    {
        $collection = collect([1,2,3]);
        $this->assertEqualsCanonicalizing([1,2,3], $collection->all());
    }

    public function testForEach(): void
    {
        $collections = collect([1,2,3,4,5,6,7,8]);
        foreach($collections as $key => $value){
            $this->assertEquals($key+1, $value);
        }
    }

    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1,2,3);
        $this->assertEqualsCanonicalizing([1,2,3], $collection->all());

        $result = $collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1,2], $collection->all());
    }

    public function testMap()
    {
        $collection = collect([1,2]);
        $result = $collection->map(fn ($item) => $item * 2);

        $this->assertEqualsCanonicalizing([2,4], $result->all());
    }

    public function testMapInto()
    {
        $collection = collect(['ahmad']);
        $result = $collection->mapInto(Person::class);

        $this->assertEquals($result->all(), [new Person('ahmad')]);
    }

    public function testMapSpread()
    {
        $collection = collect([
            ['ahmad', 'ihsan'],
            ['hanip', 'hizbulhaq']
        ]);

        $results = $collection->mapSpread(function($firsname, $lastname){
            $fullname = $firsname . ' ' . $lastname;
            return new Person($fullname);
        });

        $this->assertEquals([new Person('ahmad ihsan'), new Person('hanip hizbulhaq')], $results->all());
    }

    public function testMapIntoGroups()
    {
        $collections = collect([
            [
                "name" => "ahmad",
                "department" => "IT"
            ],
            [
                "name" => "ihsan",
                "department" => "IT"
            ],
            [
                "name" => "hanif",
                "department" => "HR"
            ]
        ]);

        $results = $collections->mapToGroups(fn ($person)=> [$person['department'] => $person['name']]);

        $this->assertEquals([   
            'IT' => collect(['ahmad', 'ihsan']),
            'HR' => collect(['hanif'])
        ], $results->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6]),
        ], $collection3->all());
    }

    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6], $collection3->all());
    }

    public function testCombine()
    {
        $collection1 = collect(["name", "country"]);
        $collection2 = collect(["Eko", "Indonesia"]);
        $collection3 = $collection1->combine($collection2);

        $this->assertEqualsCanonicalizing([
            "name" => "Eko",
            "country" => "Indonesia"
        ], $collection3->all());
    }

    public function testCollapse()
    {
        $collection = collect([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);
        $result = $collection->collapse();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                "name" => "Eko",
                "hobbies" => ["Coding", "Gaming"]
            ],
            [
                "name" => "Khannedy",
                "hobbies" => ["Reading", "Writing"]
            ],
        ]);
        $result = $collection->flatMap(function ($item) {
            $hobbies = $item["hobbies"];
            return $hobbies;
        });

        $this->assertEqualsCanonicalizing(["Coding", "Gaming", "Reading", "Writing"], $result->all());
    }

    public function testStringRepresentation()
    {
        $collection = collect(["Eko", "Khannedy", "Khannedy"]);

        $this->assertEquals("Eko-Khannedy-Khannedy", $collection->join("-"));
        $this->assertEquals("Eko-Khannedy_Khannedy", $collection->join("-", "_"));
        $this->assertEquals("Eko, Khannedy and Khannedy", $collection->join(", ", " and "));

    }

    public function testFilter()
    {
        $collection = collect([
            "Eko" => 100,
            "Budi" => 80,
            "Joko" => 90
        ]);

        $result = $collection->filter(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals([
            "Eko" => 100,
            "Joko" => 90
        ], $result->all());

    }

    public function testFilterIndex()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->filter(function ($value, $key) {
            return $value % 2 == 0;
        });

        $this->assertEqualsCanonicalizing([2, 4, 6, 8, 10], $result->all());

        // cara kerja filter untuk array index
        // filter akan tetap menganggap value dengan index yang sama sebelumnya walaupun sudah di filter
        // index => value
        // 0 => 1
        // 1 => 2
        // 2 => 3
        // 3 => 4
        // maka value 2 dan 4 akan tetap di index 1 dan 3
        // walaupun kalo kita lihat hasilnha seperti ini
        // [2,4] seakan-akan 2 dan 4 menjadi array baru dengan index 0 dan 1

    }

    public function testPartition()
    {
        $collection = collect([
            "Eko" => 100,
            "Budi" => 80,
            "Joko" => 90
        ]);

        [$result1, $result2] = $collection->partition(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals([
            "Eko" => 100,
            "Joko" => 90
        ], $result1->all());
        $this->assertEquals([
            "Budi" => 80
        ], $result2->all());

    }

    public function testTesting()
    {
        $collection = collect(['ahmad', 'ihsanullah', 'rabbani']);

        self::assertTrue($collection->contains('ahmad'));
        self::assertTrue($collection->contains(fn($value, $key)=> $value == 'rabbani'));
    }

    
}
