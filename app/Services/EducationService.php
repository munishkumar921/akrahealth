<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use SoapBox\Formatter\Formatter;

class EducationService
{
    protected string $educationFilePath;

    protected array $topics = [];

    protected array $types = [];

    public function __construct()
    {
        $this->educationFilePath = resource_path('healthwise.yaml');
    }

    /**
     * Load education topics from YAML file
     */
    protected function loadTopics(): array
    {
        if (! empty($this->topics)) {
            return $this->topics;
        }

        if (! File::exists($this->educationFilePath)) {
            $this->topics = [];

            return $this->topics;
        }

        try {
            $content = File::get($this->educationFilePath);

            // Use laravel-formatter for YAML parsing
            $formatter = Formatter::make($content, 'yaml');
            $data = $formatter->toArray();
            if (is_array($data)) {
                $this->topics = array_map(function ($item) {
                    return [
                        'topic' => $item['desc'] ?? '',
                        'url' => $item['url'] ?? '',
                        'type' => $this->categorizeTopic($item['desc'] ?? ''),
                    ];
                }, $data);

                // Filter out empty topics
                $this->topics = array_filter($this->topics, function ($item) {
                    return ! empty($item['topic']) && ! empty($item['url']);
                });

                // Re-index array
                $this->topics = array_values($this->topics);

                // Cache for 24 hours
                // Cache::put($cacheKey, $this->topics, 86400);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load education topics: '.$e->getMessage());
            $this->topics = [];
        }

        return $this->topics;
    }

    /**
     * Build full URL from healthwise path
     */
    protected function buildUrl(string $path): string
    {
        $path = trim($path);
        if (empty($path)) {
            return '';
        }

        // Remove leading '>' and whitespace
        $path = ltrim($path, '> ');
        $path = trim($path);

        if (empty($path)) {
            return '';
        }

        // If already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Build URL based on path
        if (str_starts_with($path, '/alberta/')) {
            return 'https://alberta.healthwise.net'.$path;
        }

        if (str_starts_with($path, '/health/')) {
            return 'https://myhealth.alberta.ca'.$path;
        }

        return 'https://myhealth.alberta.ca'.$path;
    }

    /**
     * Categorize a topic based on its description
     */
    protected function categorizeTopic(string $description): string
    {
        $description = strtolower($description);

        $categoryMappings = [
            'Care Instructions' => [
                'care instructions', 'care at home', 'what to expect', 'after your visit',
            ],
            'Learning About' => [
                'learning about', 'understanding', 'about this', 'about your',
            ],
            'Exercises' => [
                'exercises', 'exercise', 'stretches', 'rehabilitation',
            ],
            'Surgery' => [
                'before your surgery', 'after your surgery', 'what to expect at home',
                'surgery', 'operation', 'procedure',
            ],
            'Tests & Results' => [
                'about this test', 'about your test', 'test results', 'screening',
            ],
            'Medicines' => [
                'medicine', 'medication', 'drug', 'prescription',
            ],
            'Children' => [
                "your child's", "your baby's", "your teen's", 'children', 'pediatric', 'infant', 'baby', 'newborn', 'teen',
            ],
            'Pregnancy' => [
                'pregnancy', 'pregnant', 'prenatal', 'postpartum', 'delivery', 'birth',
            ],
            'Mental Health' => [
                'mental health', 'depression', 'anxiety', 'stress', 'emotional', 'behavioral',
            ],
            'Heart Health' => [
                'heart', 'cardiac', 'cardiovascular', 'blood pressure', 'hypertension',
            ],
            'Diabetes' => [
                'diabetes', 'diabetic', 'blood sugar', 'glucose',
            ],
            'Cancer' => [
                'cancer', 'tumor', 'oncology', 'malignant',
            ],
            'Pain Management' => [
                'pain', 'chronic pain', 'acute pain', 'discomfort',
            ],
            'Respiratory' => [
                'lung', 'respiratory', 'breathing', 'asthma', 'copd', 'pulmonary',
            ],
            'Nutrition' => [
                'nutrition', 'diet', 'eating', 'food', 'weight', 'obesity',
            ],
            'Substance Use' => [
                'smoking', 'tobacco', 'alcohol', 'drug use', 'substance', 'addiction', 'quit smoking', 'cessation',
            ],
        ];

        foreach ($categoryMappings as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    return $category;
                }
            }
        }

        return 'General Health';
    }

    /**
     * Search education topics
     */
    public function searchTopics(string $query, ?string $type = null, int $limit = 50): array
    {
        $topics = $this->loadTopics();
        if (empty($topics)) {
            return [];
        }

        $query = strtolower(trim($query));
        if (empty($query)) {
            return [];
        }

        // Split query into words for partial matching
        $queryWords = explode(' ', $query);

        $results = array_filter($topics, function ($item) use ($queryWords, $type) {
            $topicLower = strtolower($item['topic']);
            $typeMatch = true;

            // Check type filter
            if ($type && $type !== 'All') {
                $typeMatch = $item['type'] === $type;
            }

            if (! $typeMatch) {
                return false;
            }

            // Check if all query words are present in topic
            foreach ($queryWords as $word) {
                $word = trim($word);
                if (! empty($word) && ! str_contains($topicLower, $word)) {
                    return false;
                }
            }

            return true;
        });

        // Limit results
        $results = array_slice($results, 0, $limit);

        // Re-index
        return array_values($results);
    }

    /**
     * Get all available education types
     */
    public function getTypes(): array
    {
        if (! empty($this->types)) {
            return $this->types;
        }

        $this->types = [
            'All' => 'All Categories',
            'Care Instructions' => 'Care Instructions',
            'Learning About' => 'Learning About',
            'Exercises' => 'Exercises',
            'Surgery' => 'Surgery & Procedures',
            'Tests & Results' => 'Tests & Results',
            'Medicines' => 'Medicines',
            'Children' => 'Children & Teens',
            'Pregnancy' => 'Pregnancy & Birth',
            'Mental Health' => 'Mental Health',
            'Heart Health' => 'Heart Health',
            'Diabetes' => 'Diabetes',
            'Cancer' => 'Cancer',
            'Pain Management' => 'Pain Management',
            'Respiratory' => 'Respiratory Health',
            'Nutrition' => 'Nutrition & Weight',
            'Substance Use' => 'Substance Use & Addiction',
            'General Health' => 'General Health',
        ];

        return $this->types;
    }

    /**
     * Get popular/default topics to show on initial load
     */
    public function getPopularTopics(int $limit = 20): array
    {
        $topics = $this->loadTopics();

        if (empty($topics)) {
            return [];
        }

        // Return first N topics
        return array_slice($topics, 0, $limit);
    }

    /**
     * Get a single topic by URL or description
     */
    public function getTopic(string $identifier): ?array
    {
        $topics = $this->loadTopics();

        // Clean the identifier - remove > and whitespace prefix for comparison
        $cleanIdentifier = trim(ltrim($identifier, '> '));

        foreach ($topics as $topic) {
            // Clean the stored URL as well
            $cleanUrl = trim(ltrim($topic['url'], '> '));

            if ($cleanUrl === $cleanIdentifier ||
                strtolower($topic['url']) === strtolower($identifier) ||
                strtolower($topic['topic']) === strtolower($identifier) ||
                str_contains($cleanUrl, $cleanIdentifier) ||
                str_contains($cleanIdentifier, $cleanUrl)) {
                return $topic;
            }
        }

        return null;
    }

    /**
     * Clear the cache
     */
    public function clearCache(): void
    {
        Cache::forget('education_topics');
        $this->topics = [];
    }
}
