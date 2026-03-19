<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\EducationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Sunra\PhpSimple\HtmlDomParser;

class DEducationController extends Controller
{
    protected EducationService $educationService;

    public function __construct(EducationService $educationService)
    {
        $this->educationService = $educationService;
    }

    /**
     * Display the education materials management page
     */
    public function index()
    {
        return Inertia::render('Doctors/Patient/Documents/Education', []);
    }

    /**
     * Search education topics from healthwise.yaml
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('search', '');

            $type = $request->input('type', null);
            $limit = $request->input('limit', 50);

            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Please enter a search term',
                ]);
            }

            $results = $this->educationService->searchTopics($query, $type, $limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => count($results),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search education topics: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all available education types
     */
    public function getTypes(): JsonResponse
    {
        try {
            $types = $this->educationService->getTypes();

            return response()->json([
                'success' => true,
                'data' => $types,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get education types: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get popular/top education topics
     */
    public function getPopularTopics(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 20);
            $topics = $this->educationService->getPopularTopics($limit);

            return response()->json([
                'success' => true,
                'data' => $topics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get popular topics: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store selected education material
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'topic' => 'required|string|max:500',
                'description' => 'nullable|string|max:1000',
                'url' => 'nullable|string|max:1000',
                'education_type' => 'nullable|string|max:100',
            ]);

            $user = auth()->user();
            $doctor = $user->doctor ?? null;
            $hospitalId = $doctor?->hospital_id ?? $user?->hospital_id ?? null;
            $patientId = $doctor?->selected_patient_id ?? $request->input('patient_id');

            if (! $patientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patient selected',
                ], 400);
            }

            $document = Document::create([
                'type' => 'education',
                'description' => $validated['topic'],
                'url' => $validated['url'] ?? null,
                'patient_id' => $patientId,
                'hospital_id' => $hospitalId,
                'date' => date('Y-m-d'),
                'from' => 'Patient Education',
            ]);

            // Store additional metadata in description if provided
            if (! empty($validated['description'])) {
                $document->update([
                    'description' => $validated['topic'].' - '.$validated['description'],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $document->id,
                    'topic' => $document->description,
                    'url' => $document->url,
                    'date' => $document->date,
                ],
                'message' => 'Education material saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save education material: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download education material content
     *
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request)
    {
        try {
            $topic = $request->input('topic', '');
            $description = $request->input('html_content', '');

            $content = view('education.download', [
                'topic' => $topic,
                'description' => $description,
                'date' => date('F j, Y'),
            ])->render();

            return response($content, 200)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="education-'.time().'.html"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate download: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch healthwise content from external URL
     */
    public function healthwise_view(Request $request): JsonResponse
    {

        $path = trim($request->input('url'));

        if (empty($path)) {
            return response()->json(['success' => false, 'message' => 'URL parameter is required'], 400);
        }

        // prevent malformed URLs
        $url = 'https://myhealth.alberta.ca'.$path;

        // Try to fetch with a quick timeout first - if it fails, return local content

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Connection' => 'keep-alive',
        ])
            ->timeout(10)
            ->get($url);
        if ($response->successful()) {
            $responseBody = $response->body();

            // Check if response is empty or too short
            if (empty($responseBody) || strlen($responseBody) < 100) {
                throw new \Exception('Empty or too short response');
            }

            // Parse HTML using HtmlDomParser
            $html = HtmlDomParser::str_get_html($responseBody);

            if (! $html) {
                throw new \Exception('Failed to parse HTML');
            }

            $main = $html->find('div.HwContent', 0);
            if (! $main) {
                return response()->json(['success' => false, 'message' => 'Content container not found. The page structure may have changed.'], 404);
            }

            $return = $main->outertext;
            $imgs = $main->find('img');
            $url_parts = explode('/', $url);
            array_pop($url_parts);
            $base_url = implode('/', $url_parts);

            foreach ($imgs as $i => $img) {
                $img_link = $img->src;
                $img_url = str_starts_with($img_link, 'http') ? $img_link : $base_url.'/'.ltrim($img_link, '/');

                // Try to fetch image with quick timeout
                try {
                    $imgResponse = Http::timeout(5)->get($img_url);

                    if ($imgResponse->successful() && strlen($imgResponse->body()) > 0) {
                        $temp_path = public_path('temp');
                        if (! File::exists($temp_path)) {
                            File::makeDirectory($temp_path, 0755, true);
                        }

                        $file_name = time().'_img_'.$i.'.jpg';
                        $file_path = $temp_path.'/'.$file_name;
                        File::put($file_path, $imgResponse->body());

                        $new_url = asset('temp/'.$file_name);
                        $return = str_replace($img_link, $new_url, $return);
                    }
                } catch (\Exception $imgError) {
                    // Continue without this image if it fails
                    continue;
                }
            }

            // Remove anchor tags
            $links = $main->find('a.HwSectionNameTag');
            foreach ($links as $link) {
                $return = str_replace($link->outertext, '', $return);
            }

            return response()->json([
                'success' => true,
                'html' => $return,
            ]);
        }

    }

    /**
     * Process DOM node content
     */
    protected function processDomContent(\DOMNode $node): string
    {
        $content = '';

        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);

                // Skip unwanted elements
                if (in_array($tag, ['script', 'style', 'nav', 'header', 'footer', 'aside'])) {
                    continue;
                }

                // Skip HwSectionNameTag links
                if ($child->getAttribute('class') && stripos($child->getAttribute('class'), 'HwSectionNameTag') !== false) {
                    continue;
                }
            }

            // Process text nodes
            if ($child instanceof \DOMText) {
                $text = trim($child->textContent);
                if (! empty($text)) {
                    $content .= '<p>'.htmlspecialchars($text).'</p>';
                }
            }
            // Process element nodes recursively
            elseif ($child instanceof \DOMElement) {
                $innerContent = $this->processDomContent($child);
                if (! empty($innerContent)) {
                    $content .= $innerContent;
                }
            }
        }

        return $content;
    }
}
