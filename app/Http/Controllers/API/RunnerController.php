<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Single-problem, single-JSON judge controller (dummy/stub runtime).
 * - One "Sum Two Integers" question
 * - Per-language snippets
 * - Per-language restriction overrides owned by THIS problem
 * - Samples + hidden test cases
 * - Exact/trim comparator
 */
class JudgeController extends Controller
{
    private const VERSION = '1.0.0';

    /**
     * A single problem "package" JSON holding ALL dependencies for the frontend.
     * Later: load this from DB; the shape can remain identical.
     */
    private array $PROBLEM = [
        'version'     => self::VERSION,
        'id'          => 'sum-two-integers',
        'slug'        => 'sum-two-integers',
        'title'       => 'Sum Two Integers',
        'difficulty'  => 'easy',
        'statement'   =>
            "Read two integers from standard input and print their sum.\n".
            "Input: two integers separated by space or newline.\n".
            "Output: the sum as an integer followed by a newline.",

        // Which languages are allowed for THIS problem
        'languages'   => ['c','cpp','java','python'],

        // Language-specific default code templates and entry hints
        'snippets'    => [
            'c' => [
                'entry_hint' => 'C entry: main',
                'template' => <<<C
#include <stdio.h>

int main() {
    long long a, b;
    if (scanf("%lld %lld", &a, &b) != 2) {
        // fallback: try reading line by line
        if (scanf("%lld", &a) != 1) return 0;
        if (scanf("%lld", &b) != 1) return 0;
    }
    printf("%lld\\n", a + b);
    return 0;
}
C
            ],
            'cpp' => [
                'entry_hint' => 'C++ entry: main()',
                'template' => <<<CPP
#include <bits/stdc++.h>
using namespace std;

int main(){
    ios::sync_with_stdio(false);
    cin.tie(nullptr);
    long long a,b;
    if(!(cin>>a)) return 0;
    if(!(cin>>b)) return 0;
    cout << (a + b) << "\\n";
    return 0;
}
CPP
            ],
            'java' => [
                'entry_hint' => 'Java class: Main with public static void main(String[] args)',
                'template' => <<<JAVA
import java.io.*;
import java.util.*;

public class Main {
    public static void main(String[] args) throws Exception {
        BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
        String s = br.readLine();
        String t = (s == null) ? null : br.readLine();
        long a, b;
        if (s != null && s.trim().contains(" ")) {
            String[] p = s.trim().split("\\\\s+");
            a = Long.parseLong(p[0]);
            b = Long.parseLong(p[1]);
        } else {
            if (s == null) return;
            a = Long.parseLong(s.trim());
            if (t == null) return;
            b = Long.parseLong(t.trim());
        }
        System.out.println(a + b);
    }
}
JAVA
            ],
            'python' => [
                'entry_hint' => 'Python 3: script top-level',
                'template' => <<<PY
import sys

data = sys.stdin.read().strip().split()
if not data:
    sys.exit(0)
if len(data) == 1:
    a = int(data[0])
    b = int(sys.stdin.readline().strip())
else:
    a = int(data[0]); b = int(data[1])
print(a + b)
PY
            ],
        ],

        /**
         * Per-language RESTRICTIONS for THIS problem (override global defaults).
         * You can tune these per problem as needed.
         */
        'language_limits' => [
            'c' => [
                'line_limit'       => 120,
                'byte_limit'       => 12000,
                'max_inputs'       => 8,
                'max_stdin_tokens' => 100,
                'max_args'         => 6,
                'time_ms_max'      => 3000,
                'mem_kb_max'       => 196608,
                'allow_label'      => 'headers',
                'allow'            => ['stdio.h','stdlib.h','string.h','math.h'],
                'forbid_regex'     => ['\\b(system|popen|fork|exec)\\s*\\('],
            ],
            'cpp' => [
                'line_limit'       => 140,
                'byte_limit'       => 15000,
                'max_inputs'       => 8,
                'max_stdin_tokens' => 100,
                'max_args'         => 6,
                'time_ms_max'      => 3000,
                'mem_kb_max'       => 196608,
                'allow_label'      => 'headers',
                'allow'            => ['bits/stdc++.h','iostream','vector','string','map','unordered_map','set','queue','stack','cmath','algorithm','numeric','iomanip'],
                'forbid_regex'     => ['\\b(system|popen|fork|exec)\\s*\\('],
            ],
            'java' => [
                'line_limit'       => 180,
                'byte_limit'       => 20000,
                'max_inputs'       => 8,
                'max_stdin_tokens' => 100,
                'max_args'         => 6,
                'time_ms_max'      => 4000,
                'mem_kb_max'       => 262144,
                'allow_label'      => 'imports',
                'allow'            => ['java.io.*','java.util.*','java.math.*'],
                'forbid_regex'     => ['Runtime\\.getRuntime\\s*\\(','ProcessBuilder\\s*\\('],
            ],
            'python' => [
                'line_limit'       => 160,
                'byte_limit'       => 12000,
                'max_inputs'       => 8,
                'max_stdin_tokens' => 100,
                'max_args'         => 6,
                'time_ms_max'      => 3000,
                'mem_kb_max'       => 196608,
                'allow_label'      => 'modules',
                'allow'            => ['sys'],
                'forbid_regex'     => ['\\b(os|subprocess|socket|asyncio|multiprocessing)\\b'],
            ],
        ],

        // I/O & grading rules
        'io' => [
            'input_format'  => "Two integers a and b (space or newline separated).",
            'output_format' => "Single integer (a+b) followed by a newline.",
        ],
        'grading' => [
            'comparator'     => 'exact-trim', // exact match after trimming trailing newlines/spaces
            'case_sensitive' => true,
            'strip_trailing_newlines' => true,
        ],

        // Tests = samples (shown) + hidden (not shown on UI, but run by judge)
        'tests' => [
            'samples' => [
                ['id' => 'S1', 'input' => "1 2\n",  'output' => "3\n"],
                ['id' => 'S2', 'input' => "5\n7\n", 'output' => "12\n"],
            ],
            'hidden' => [
                ['id' => 'H1', 'input' => "-3 4\n", 'output' => "1\n"],
                ['id' => 'H2', 'input' => "1000000000 1000000000\n", 'output' => "2000000000\n"],
            ],
        ],
    ];

    /* ============================ Public APIs ============================ */

    /**
     * GET /api/judge/problem
     * Returns the single problem package JSON with snippets, limits, tests, etc.
     * Optional query: ?language=python  -> limits/snippet filtered to that lang (others removed)
     */
    public function getProblem(Request $r)
    {
        $lang = trim((string) $r->query('language', ''));

        $pkg = $this->PROBLEM;

        if ($lang !== '') {
            if (!in_array($lang, $pkg['languages'], true)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Language not supported for this problem: {$lang}",
                    'errors'  => ['language' => ['Supported: '.implode(', ', $pkg['languages'])]],
                ], 422);
            }
            // Filter to one language to reduce payload when UI loads per-tab
            $pkg['languages'] = [$lang];
            $pkg['snippets']  = [$lang => $pkg['snippets'][$lang]];
            $pkg['language_limits'] = [$lang => $pkg['language_limits'][$lang]];
        }

        return response()->json([
            'status' => 'success',
            'data'   => $pkg,
        ], 200);
    }

    /**
     * POST /api/judge/submit
     * Body: {
     *   language: "c|cpp|java|python",
     *   code: "string",
     *   // optional power user fields (ignored/validated but restricted by problem limits):
     *   stdin_mode: "single|batch" (default single => we will use tests instead),
     *   args: ["..."], time_limit_ms: 123, memory_limit_kb: 456,
     *   include_samples: true|false (default true, runs both samples+hidden)
     * }
     *
     * Runs ALL defined test cases for THIS problem under per-language restrictions (dummy runtime),
     * returns pass/fail counts and per-case run detail (stdout, stderr, time, mem).
     */
    public function submit(Request $r)
    {
        $v = Validator::make($r->all(), [
            'language'        => 'required|in:c,cpp,java,python',
            'code'            => 'required|string|min:1',
            'stdin_mode'      => 'nullable|in:single,batch',
            'args'            => 'nullable|array',
            'args.*'          => 'string',
            'time_limit_ms'   => 'nullable|integer|min:1',
            'memory_limit_kb' => 'nullable|integer|min:1',
            'include_samples' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }

        $data = $v->validated();
        $lang = $data['language'];

        // Check language allowed for this problem
        if (!in_array($lang, $this->PROBLEM['languages'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Language not supported for this problem: {$lang}",
                'errors'  => ['language' => ['Supported: '.implode(', ', $this->PROBLEM['languages'])]],
            ], 422);
        }

        $limits = $this->PROBLEM['language_limits'][$lang] ?? [];

        // Enforce per-problem restrictions BEFORE running
        $restrict = $this->enforceRestrictions(
            $lang,
            $data['code'],
            null,               // stdin not used here; tests supply
            null,
            $data['args'] ?? [],
            $data['time_limit_ms'] ?? null,
            $data['memory_limit_kb'] ?? null,
            $limits
        );

        if (!$restrict['ok']) {
            return response()->json([
                'status'       => 'error',
                'message'      => $restrict['errors'][0] ?? 'Restriction violation',
                'diagnostics'  => $restrict['diagnostics'],
                'limits'       => $limits,
            ], 422);
        }

        // Build test list (samples + hidden unless excluded)
        $includeSamples = array_key_exists('include_samples', $data) ? (bool)$data['include_samples'] : true;
        $tests = [];
        if ($includeSamples) {
            foreach ($this->PROBLEM['tests']['samples'] as $t) $tests[] = $t + ['visibility' => 'sample'];
        }
        foreach ($this->PROBLEM['tests']['hidden'] as $t) $tests[] = $t + ['visibility' => 'hidden'];

        // Run all tests (dummy simulator)
        $results = [];
        $pass = 0; $fail = 0;
        $timeTotal = 0; $memMax = 0;

        foreach ($tests as $tc) {
            $run = $this->simulateRun($lang, $data['code'], $tc['input'], $data['args'] ?? []);
            $timeTotal += (int)($run['time_ms'] ?? 0);
            $memMax = max($memMax, (int)($run['memory_kb'] ?? 0));

            $actual = $run['stdout'] ?? '';
            $expect = $tc['output'];

            $ok = $this->compare($actual, $expect, $this->PROBLEM['grading']);
            if ($ok) $pass++; else $fail++;

            $results[] = [
                'test_id'       => $tc['id'],
                'visibility'    => $tc['visibility'],
                'input'         => $tc['visibility'] === 'sample' ? $tc['input'] : null, // hide hidden inputs if you prefer
                'expected'      => $tc['visibility'] === 'sample' ? $expect : null,
                'stdout'        => $run['stdout'] ?? '',
                'stderr'        => $run['stderr'] ?? '',
                'compile_stdout'=> $run['compile_stdout'] ?? '',
                'compile_stderr'=> $run['compile_stderr'] ?? '',
                'diagnostics'   => $run['diagnostics'] ?? [],
                'exit_code'     => $run['exit_code'],
                'time_ms'       => $run['time_ms'],
                'memory_kb'     => $run['memory_kb'],
                'status'        => $ok ? 'passed' : 'failed',
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'problem_id'   => $this->PROBLEM['id'],
                'language'     => $lang,
                'limits'       => $limits,
                'grading'      => $this->PROBLEM['grading'],
                'summary'      => [
                    'total'     => count($tests),
                    'passed'    => $pass,
                    'failed'    => $fail,
                    'time_ms'   => $timeTotal,
                    'memory_kb' => $memMax,
                ],
                'results'      => $results,
            ],
        ], 200);
    }

    /* ============================== Helpers ============================== */

    private function compare(string $actual, string $expected, array $rules): bool
    {
        $a = $actual;
        $e = $expected;

        if (!empty($rules['strip_trailing_newlines'])) {
            $a = rtrim($a, "\r\n");
            $e = rtrim($e, "\r\n");
        }
        if (($rules['comparator'] ?? '') === 'exact-trim') {
            $a = trim($a);
            $e = trim($e);
        }

        if (empty($rules['case_sensitive'])) {
            $a = mb_strtolower($a);
            $e = mb_strtolower($e);
        }
        return $a === $e;
    }

    private function diag(string $severity, string $code, string $message): array
    {
        return [
            'severity' => $severity,
            'code'     => $code,
            'message'  => $message,
            'file'     => null,
            'line'     => null,
            'column'   => null,
        ];
    }

    private function countTokens(string $s): int
    {
        $s = trim($s);
        if ($s === '') return 0;
        return count(preg_split('/\s+/u', $s, -1, PREG_SPLIT_NO_EMPTY));
    }

    private function parseCIncludes(string $code): array
    {
        $out = [];
        if (preg_match_all('/#\s*include\s*<\s*([^>\s]+)\s*>/i', $code, $m)) {
            foreach ($m[1] as $hdr) $out[] = trim($hdr);
        }
        return array_values(array_unique($out));
    }

    private function parseJavaImports(string $code): array
    {
        $imports = [];
        if (preg_match_all('/^\s*import\s+([a-zA-Z0-9_.\*]+)\s*;/m', $code, $m)) {
            foreach ($m[1] as $imp) $imports[] = trim($imp);
        }
        return array_values(array_unique($imports));
    }

    private function diffImports(array $imports, array $allow): array
    {
        // allow items can be package.*; accept if import starts with an allowed prefix
        $bad = [];
        foreach ($imports as $imp) {
            $ok = false;
            foreach ($allow as $pat) {
                $prefix = rtrim($pat, '*');
                if ($prefix === '') continue;
                if (str_starts_with($imp, $prefix)) { $ok = true; break; }
            }
            if (!$ok) $bad[] = $imp;
        }
        return $bad;
    }

    private function parsePythonImports(string $code): array
    {
        $mods = [];
        if (preg_match_all('/^\s*import\s+([a-zA-Z0-9_]+)(\s+as\s+[a-zA-Z0-9_]+)?/m', $code, $m)) {
            foreach ($m[1] as $mod) $mods[] = $mod;
        }
        if (preg_match_all('/^\s*from\s+([a-zA-Z0-9_]+)\s+import\s+/m', $code, $m2)) {
            foreach ($m2[1] as $mod) $mods[] = $mod;
        }
        return array_values(array_unique($mods));
    }

    /**
     * Enforce THIS problem's per-language restrictions; return diagnostics if violations.
     */
    private function enforceRestrictions(
        string $lang,
        string $code,
        ?string $stdin,
        ?array  $inputs,
        array   $args,
        ?int    $timeLimitMs,
        ?int    $memKb,
        array   $limits
    ): array {
        $errors = [];
        $diagnostics = [];

        // Basic sizes
        $lineCount = substr_count($code, "\n") + 1;
        $byteLen   = strlen($code);
        if ($lineCount > ($limits['line_limit'] ?? PHP_INT_MAX)) {
            $errors[] = "Too many lines: {$lineCount} > {$limits['line_limit']}";
            $diagnostics[] = $this->diag('error', 'LINE_LIMIT', "Source has {$lineCount} lines, limit is {$limits['line_limit']}");
        }
        if ($byteLen > ($limits['byte_limit'] ?? PHP_INT_MAX)) {
            $errors[] = "Source too large: {$byteLen} bytes > {$limits['byte_limit']}";
            $diagnostics[] = $this->diag('error', 'BYTE_LIMIT', "Source has {$byteLen} bytes, limit is {$limits['byte_limit']}");
        }

        // Args
        if (count($args) > ($limits['max_args'] ?? PHP_INT_MAX)) {
            $errors[] = "Too many args: ".count($args)." > {$limits['max_args']}";
            $diagnostics[] = $this->diag('error', 'ARGS_LIMIT', "Arguments exceed limit {$limits['max_args']}");
        }

        // Time/memory caps (user cannot exceed problem caps)
        if ($timeLimitMs && $timeLimitMs > ($limits['time_ms_max'] ?? PHP_INT_MAX)) {
            $errors[] = "Time limit too high: {$timeLimitMs}ms > {$limits['time_ms_max']}ms";
            $diagnostics[] = $this->diag('error', 'TIME_LIMIT', "Time limit exceeds maximum {$limits['time_ms_max']} ms");
        }
        if ($memKb && $memKb > ($limits['mem_kb_max'] ?? PHP_INT_MAX)) {
            $errors[] = "Memory limit too high: {$memKb}KB > {$limits['mem_kb_max']}KB";
            $diagnostics[] = $this->diag('error', 'MEM_LIMIT', "Memory limit exceeds maximum {$limits['mem_kb_max']} KB");
        }

        // Inputs (not used here; tests drive stdin) — still validate shape if present
        if (is_array($inputs)) {
            if (count($inputs) > ($limits['max_inputs'] ?? PHP_INT_MAX)) {
                $errors[] = "Too many batch inputs: ".count($inputs)." > {$limits['max_inputs']}";
                $diagnostics[] = $this->diag('error', 'INPUTS_LIMIT', "Batch inputs exceed limit {$limits['max_inputs']}");
            }
            foreach ($inputs as $i => $inp) {
                $tok = $this->countTokens($inp);
                if ($tok > ($limits['max_stdin_tokens'] ?? PHP_INT_MAX)) {
                    $errors[] = "Input #".($i+1)." too long ({$tok} tokens), limit {$limits['max_stdin_tokens']}";
                    $diagnostics[] = $this->diag('error', 'STDIN_TOKENS', "Input #".($i+1)." has {$tok} tokens; limit {$limits['max_stdin_tokens']}");
                    break;
                }
            }
        } elseif ($stdin !== null) {
            $tok = $this->countTokens($stdin);
            if ($tok > ($limits['max_stdin_tokens'] ?? PHP_INT_MAX)) {
                $errors[] = "stdin too long ({$tok} tokens), limit {$limits['max_stdin_tokens']}";
                $diagnostics[] = $this->diag('error', 'STDIN_TOKENS', "stdin has {$tok} tokens; limit {$limits['max_stdin_tokens']}");
            }
        }

        // Language-specific allow/forbid checks (very lightweight)
        if ($lang === 'c' || $lang === 'cpp') {
            $includes = $this->parseCIncludes($code);
            $allowed = $limits['allow'] ?? [];
            if (!empty($allowed)) {
                $notAllowed = array_diff($includes, $allowed);
                if ($notAllowed) {
                    $errors[] = "Disallowed ".($limits['allow_label'] ?? 'headers').": ".implode(', ', $notAllowed);
                    foreach ($notAllowed as $hdr) {
                        $diagnostics[] = $this->diag('error', 'DISALLOWED_HEADER', "Header not allowed: {$hdr}");
                    }
                }
            }
        } elseif ($lang === 'java') {
            $imports = $this->parseJavaImports($code);
            $notAllowed = $this->diffImports($imports, $limits['allow'] ?? []);
            if (($limits['allow'] ?? []) && $notAllowed) {
                $errors[] = "Disallowed ".($limits['allow_label'] ?? 'imports').": ".implode(', ', $notAllowed);
                foreach ($notAllowed as $imp) {
                    $diagnostics[] = $this->diag('error', 'DISALLOWED_IMPORT', "Import not allowed: {$imp}");
                }
            }
        } elseif ($lang === 'python') {
            $mods = $this->parsePythonImports($code);
            $allowed = $limits['allow'] ?? [];
            if (!empty($allowed)) {
                $notAllowed = array_diff($mods, $allowed);
                if ($notAllowed) {
                    $errors[] = "Disallowed ".($limits['allow_label'] ?? 'modules').": ".implode(', ', $notAllowed);
                    foreach ($notAllowed as $m) {
                        $diagnostics[] = $this->diag('error', 'DISALLOWED_MODULE', "Module not allowed: {$m}");
                    }
                }
            }
        }

        // Forbidden patterns (regex)
        foreach (($limits['forbid_regex'] ?? []) as $rx) {
            if (@preg_match("/{$rx}/", $code)) {
                if (preg_match("/{$rx}/", $code)) {
                    $errors[] = "Forbidden API/pattern matched: /{$rx}/";
                    $diagnostics[] = $this->diag('error', 'FORBIDDEN_PATTERN', "Pattern matched: /{$rx}/");
                }
            }
        }

        return [
            'ok'           => empty($errors),
            'errors'       => $errors,
            'diagnostics'  => $diagnostics,
        ];
    }

    /**
     * Simulated compile+run (no real sandbox). Mirrors RunnerController stub behavior.
     * - Validates trivial entry presence for C/C++/Java/Python syntax sanity.
     * - If stdin has two ints, prints sum (as if user solved it).
     */
    private function simulateRun(string $lang, string $code, string $stdin, array $args): array
    {
        $stdout = '';
        $stderr = '';
        $compileStdout = '';
        $compileStderr = '';
        $diagnostics = [];
        $exit = null;
        $timeMs = rand(3, 15);
        $memKb  = 1024 + rand(0, 2048);

        // Entry checks
        if ($lang === 'c' || $lang === 'cpp') {
            if (!preg_match('/\bmain\s*\(/', $code)) {
                $compileStderr =
                    ($lang === 'c' ? "main.c" : "main.cpp") . ":1:1: error: undefined reference to `main`\n" .
                    "collect2: error: ld returned 1 exit status\n";
                $diagnostics[] = [
                    'severity' => 'error',
                    'message'  => 'undefined reference to main',
                    'file'     => $lang === 'c' ? 'main.c' : 'main.cpp',
                    'line'     => 1, 'column' => 1, 'code' => 'LINKER_MAIN_MISSING',
                ];
                return [
                    'stdout'          => '',
                    'stderr'          => '',
                    'exit_code'       => null,
                    'time_ms'         => $timeMs,
                    'memory_kb'       => $memKb,
                    'compile_stdout'  => $compileStdout,
                    'compile_stderr'  => $compileStderr,
                    'diagnostics'     => $diagnostics,
                    'mode'            => 'stub',
                ];
            }
        } elseif ($lang === 'java') {
            $hasMainClass = preg_match('/\bclass\s+Main\b/', $code);
            $hasEntry     = preg_match('/public\s+static\s+void\s+main\s*\(\s*String\s*\[\]\s*\w*\s*\)/', $code);
            if (!($hasMainClass && $hasEntry)) {
                $compileStderr = "Main.java:1: error: class Main with public static void main(String[] args) not found\n1 error\n";
                $diagnostics[] = [
                    'severity' => 'error',
                    'message'  => 'class Main with public static void main(String[] args) not found',
                    'file'     => 'Main.java',
                    'line'     => 1, 'column' => 1, 'code' => 'JAVA_MAIN_MISSING',
                ];
                return [
                    'stdout'          => '',
                    'stderr'          => '',
                    'exit_code'       => null,
                    'time_ms'         => $timeMs,
                    'memory_kb'       => $memKb,
                    'compile_stdout'  => '',
                    'compile_stderr'  => $compileStderr,
                    'diagnostics'     => $diagnostics,
                    'mode'            => 'stub',
                ];
            }
        } elseif ($lang === 'python') {
            // Toy syntax trap example
            if (preg_match('/^\s*print\s*\(.+\s*$/m', $code)) {
                $stderr = "  File \"Main.py\", line 1\n    print(1+1\n              ^\nSyntaxError: ')' was never closed\n";
                $diagnostics[] = [
                    'severity' => 'error',
                    'message'  => "SyntaxError: ')' was never closed",
                    'file'     => 'Main.py',
                    'line'     => 1, 'column' => 1, 'code' => 'PY_SYNTAX',
                ];
                return [
                    'stdout'          => '',
                    'stderr'          => $stderr,
                    'exit_code'       => 1,
                    'time_ms'         => $timeMs,
                    'memory_kb'       => $memKb,
                    'compile_stdout'  => '',
                    'compile_stderr'  => '',
                    'diagnostics'     => $diagnostics,
                    'mode'            => 'stub',
                ];
            }
        }

        // "Run" behavior: if stdin has exactly two ints, output sum; else echo info
        if (preg_match('/^\s*(-?\d+)\s+(-?\d+)\s*$/', trim($stdin), $m)) {
            $a = (int)$m[1]; $b = (int)$m[2];
            $stdout = (string)($a + $b) . "\n";
            $exit = 0;
        } else {
            $stdout = ($stdin === '') ? "ok\n" : "read ".strlen($stdin)." bytes\n";
            $exit = 0;
        }

        // Example warning for printf("")
        if (($lang === 'c' || $lang === 'cpp') && preg_match('/printf\s*\(\s*"\s*"\s*\)/', $code)) {
            $compileStderr = ($lang==='c'?'main.c':'main.cpp').":10:5: warning: zero-length format string [-Wformat-zero-length]\n";
            $diagnostics[] = [
                'severity' => 'warning',
                'message'  => 'zero-length format string',
                'file'     => $lang==='c'?'main.c':'main.cpp',
                'line'     => 10, 'column' => 5, 'code' => 'WFORMAT_ZERO_LENGTH',
            ];
        }

        return [
            'stdout'          => $stdout,
            'stderr'          => $stderr,
            'exit_code'       => $exit,
            'time_ms'         => $timeMs,
            'memory_kb'       => $memKb,
            'compile_stdout'  => $compileStdout,
            'compile_stderr'  => $compileStderr,
            'diagnostics'     => $diagnostics,
            'mode'            => 'stub',
        ];
    }
}
