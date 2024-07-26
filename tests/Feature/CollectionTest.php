<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\LazyCollection;
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

    public function testGrouping()
    {
        $collection = collect([
            [
                "name" => "Eko",
                "department" => "IT"
            ],
            [
                "name" => "Khannedy",
                "department" => "IT"
            ],
            [
                "name" => "Budi",
                "department" => "HR"
            ]
        ]);

        $result = $collection->groupBy(["department"]);

        $this->assertEquals([
            "IT" => collect([
                [
                    "name" => "Eko",
                    "department" => "IT"
                ],
                [
                    "name" => "Khannedy",
                    "department" => "IT"
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "Budi",
                    "department" => "HR"
                ]
            ])
        ], $result->all());

        $result = $collection->groupBy(function ($value, $key) {
            return strtolower($value["department"]);
        });

        $this->assertEquals([
            "it" => collect([
                [
                    "name" => "Eko",
                    "department" => "IT"
                ],
                [
                    "name" => "Khannedy",
                    "department" => "IT"
                ]
            ]),
            "hr" => collect([
                [
                    "name" => "Budi",
                    "department" => "HR"
                ]
            ])
        ], $result->all());

    }

    public function testSlicing()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3);

        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->slice(3, 2);
        $this->assertEqualsCanonicalizing([4, 5], $result->all());

    }

    public function testTake()
    {
        $collection = collect([1, 2, 3, 1, 2, 3, 1, 2, 3]);

        $result = $collection->take(3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->skip(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

    }
    
    public function testChunk()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collection->chunk(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }

    public function testFirst()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->first();
        $this->assertEquals(1, $result);

        $result = $collection->first(function ($value, $key) {
            return $value > 5;
        });
        $this->assertEquals(6, $result);

    }

    public function testLast()
    {

        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->last();
        $this->assertEquals(9, $result);

        $result = $collection->last(function ($value, $key) {
            return $value < 5;
        });
        $this->assertEquals(4, $result);
    }

    public function testRandom()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->random();

        $this->assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9]));
        $this->assertTrue($collection->contains($result));

        // $result = $collection->random(5);
        // $this->assertEqualsCanonicalizing([1,2,3,4,5], $result->all());

    }

    public function testCheckingExistence()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(1));
        $this->assertFalse($collection->contains(10));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value == 8;
        }));

    }

    public function testOrdering()
    {
        $collection = collect([1, 3, 2, 4, 6, 5, 8, 7, 9]);
        $result = $collection->sort();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->sortDesc();
        $this->assertEqualsCanonicalizing([9, 8, 7, 6, 5, 4, 3, 2, 1], $result->all());

    }

    public function testAggregate()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->sum();
        $this->assertEquals(45, $result);

        $result = $collection->avg();
        $this->assertEquals(5, $result);

        $result = $collection->min();
        $this->assertEquals(1, $result);

        $result = $collection->max();
        $this->assertEquals(9, $result);

    }

    public function testReduce()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->reduce(function ($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals(45, $result);

        // reduce(1,2) = 3
        // reduce(3,3) = 6
        // reduce(6,4) = 10
        // reduce(10,5) = 15
        // reduce(15,6) = 21
        // reduce(21,7) = 28

    }

    public function testLazyCollection()
    {

        $collection = LazyCollection::make(function () {
            $value = 0;

            while (true) {
                yield $value;
                $value++;
            }
        });

        $result = $collection->take(10);
        $this->assertEqualsCanonicalizing([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

    }
}
