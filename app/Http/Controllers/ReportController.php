<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyGroup;
use App\Services\SurveyReportGenerator;
use App\Services\GroupReportGenerator;
use App\Services\VoteExcelExporter;
use App\Services\SuspiciousTokenDetector;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected $surveyReportGenerator;
    protected $groupReportGenerator;
    protected $voteExcelExporter;
    protected $suspiciousTokenDetector;

    public function __construct(
        SurveyReportGenerator $surveyReportGenerator,
        GroupReportGenerator $groupReportGenerator,
        VoteExcelExporter $voteExcelExporter,
        SuspiciousTokenDetector $suspiciousTokenDetector
    ) {
        $this->surveyReportGenerator = $surveyReportGenerator;
        $this->groupReportGenerator = $groupReportGenerator;
        $this->voteExcelExporter = $voteExcelExporter;
        $this->suspiciousTokenDetector = $suspiciousTokenDetector;
    }

    /**
     * Mostrar reporte de una encuesta individual
     */
    public function showSurveyReport(Survey $survey)
    {
        // Generar reporte completo
        $report = $this->surveyReportGenerator->generate($survey);

        return view('admin.reports.survey', [
            'survey' => $survey,
            'report' => $report,
        ]);
    }

    /**
     * Exportar reporte de encuesta a PDF
     */
    public function exportSurveyReport(Survey $survey)
    {
        // Generar reporte completo
        $report = $this->surveyReportGenerator->generate($survey);

        $pdf = Pdf::loadView('admin.reports.survey-pdf', [
            'survey' => $survey,
            'report' => $report,
        ]);

        return $pdf->download('reporte-' . $survey->public_slug . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Exportar reporte de encuesta a Excel/CSV
     */
    public function exportSurveyReportCsv(Survey $survey)
    {
        $report = $this->surveyReportGenerator->generate($survey);

        $filename = 'reporte-' . $survey->public_slug . '-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($survey, $report) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, ['REPORTE DE ENCUESTA: ' . $survey->title]);
            fputcsv($file, ['Fecha generación: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Estadísticas básicas
            fputcsv($file, ['ESTADÍSTICAS GENERALES']);
            fputcsv($file, ['Métrica', 'Valor']);
            fputcsv($file, ['Vistas totales', $report['basic_stats']['total_views']]);
            fputcsv($file, ['Votos enviados', $report['basic_stats']['total_votes_submitted']]);
            fputcsv($file, ['Votos válidos', $report['basic_stats']['valid_votes']]);
            fputcsv($file, ['Votos pendientes revisión', $report['basic_stats']['pending_review']]);
            fputcsv($file, ['Votos rechazados', $report['basic_stats']['rejected_votes']]);
            fputcsv($file, ['Votos no contados', $report['basic_stats']['not_counted_votes']]);
            fputcsv($file, ['Votos duplicados/fraudulentos', $report['basic_stats']['duplicate_or_fraudulent']]);
            fputcsv($file, ['Votantes únicos', $report['basic_stats']['unique_voters']]);
            fputcsv($file, []);

            // Conversión
            fputcsv($file, ['MÉTRICAS DE CONVERSIÓN']);
            fputcsv($file, ['Métrica', 'Porcentaje']);
            fputcsv($file, ['Vistas → Votos', $report['conversion_metrics']['view_to_vote_rate'] . '%']);
            fputcsv($file, ['Votos → Aprobados', $report['conversion_metrics']['vote_approval_rate'] . '%']);
            fputcsv($file, ['Conversión completa', $report['conversion_metrics']['complete_conversion_rate'] . '%']);
            fputcsv($file, []);

            // Estadísticas por pregunta
            fputcsv($file, ['ESTADÍSTICAS POR PREGUNTA']);
            foreach ($report['question_stats'] as $questionStat) {
                fputcsv($file, []);
                fputcsv($file, ['Pregunta: ' . $questionStat['question_text']]);
                fputcsv($file, ['Total votos: ' . $questionStat['total_votes']]);
                fputcsv($file, ['Opción', 'Votos', 'Porcentaje']);

                foreach ($questionStat['options'] as $option) {
                    fputcsv($file, [
                        $option['option_text'],
                        $option['votes'],
                        $option['percentage'] . '%'
                    ]);
                }
            }
            fputcsv($file, []);

            // Tokens duplicados
            if ($report['fraud_stats']['duplicate_token_stats']['total_tokens_with_duplicates'] > 0) {
                fputcsv($file, ['TOKENS CON INTENTOS DUPLICADOS']);
                fputcsv($file, ['Total tokens con duplicados', $report['fraud_stats']['duplicate_token_stats']['total_tokens_with_duplicates']]);
                fputcsv($file, ['Total intentos duplicados', $report['fraud_stats']['duplicate_token_stats']['total_duplicate_attempts']]);
                fputcsv($file, []);

                fputcsv($file, ['Token', 'Intentos', 'Estado', 'Último Intento']);
                foreach ($report['fraud_stats']['duplicate_token_stats']['top_duplicate_tokens'] as $tokenData) {
                    fputcsv($file, [
                        $tokenData['token'],
                        $tokenData['attempts'],
                        $tokenData['status'],
                        $tokenData['last_attempt_at'] ?? 'N/A'
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mostrar reporte de un grupo de encuestas
     */
    public function showGroupReport(SurveyGroup $group)
    {
        // Generar reporte completo del grupo
        $report = $this->groupReportGenerator->generate($group);

        return view('admin.reports.group', [
            'group' => $group,
            'report' => $report,
        ]);
    }

    /**
     * Exportar reporte de grupo a PDF
     */
    public function exportGroupReport(SurveyGroup $group)
    {
        // Generar reporte completo del grupo
        $report = $this->groupReportGenerator->generate($group);

        $pdf = Pdf::loadView('admin.reports.group-pdf', [
            'group' => $group,
            'report' => $report,
        ]);

        return $pdf->download('reporte-grupo-' . $group->slug . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Exportar reporte de grupo a Excel/CSV
     */
    public function exportGroupReportCsv(SurveyGroup $group)
    {
        $report = $this->groupReportGenerator->generate($group);

        $filename = 'reporte-grupo-' . $group->slug . '-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($group, $report) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, ['REPORTE DE GRUPO: ' . $group->name]);
            fputcsv($file, ['Fecha generación: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Estadísticas básicas
            fputcsv($file, ['ESTADÍSTICAS GENERALES DEL GRUPO']);
            fputcsv($file, ['Métrica', 'Valor']);
            fputcsv($file, ['Total de encuestas', $report['basic_stats']['total_surveys']]);
            fputcsv($file, ['Vistas totales', $report['basic_stats']['total_views']]);
            fputcsv($file, ['Votos enviados', $report['basic_stats']['total_votes_submitted']]);
            fputcsv($file, ['Votos válidos', $report['basic_stats']['valid_votes']]);
            fputcsv($file, ['Votos pendientes revisión', $report['basic_stats']['pending_review']]);
            fputcsv($file, ['Votos rechazados', $report['basic_stats']['rejected_votes']]);
            fputcsv($file, ['Votos no contados', $report['basic_stats']['not_counted_votes']]);
            fputcsv($file, ['Votos duplicados/fraudulentos', $report['basic_stats']['duplicate_or_fraudulent']]);
            fputcsv($file, ['Votantes únicos', $report['basic_stats']['unique_voters']]);
            fputcsv($file, []);

            // Conversión
            fputcsv($file, ['MÉTRICAS DE CONVERSIÓN']);
            fputcsv($file, ['Métrica', 'Porcentaje']);
            fputcsv($file, ['Vistas → Votos', $report['conversion_metrics']['view_to_vote_rate'] . '%']);
            fputcsv($file, ['Votos → Aprobados', $report['conversion_metrics']['vote_approval_rate'] . '%']);
            fputcsv($file, ['Conversión completa', $report['conversion_metrics']['complete_conversion_rate'] . '%']);
            fputcsv($file, []);

            // Estadísticas por encuesta
            fputcsv($file, ['ESTADÍSTICAS POR ENCUESTA']);
            fputcsv($file, ['Título', 'Vistas', 'Votos Válidos', 'Tasa de Conversión']);
            foreach ($report['per_survey_stats'] as $surveyStat) {
                fputcsv($file, [
                    $surveyStat['survey_title'],
                    $surveyStat['views'],
                    $surveyStat['valid_votes'],
                    $surveyStat['conversion_rate'] . '%'
                ]);
            }
            fputcsv($file, []);

            // Estadísticas por pregunta (agregadas)
            fputcsv($file, ['ESTADÍSTICAS AGREGADAS POR PREGUNTA']);
            foreach ($report['question_stats'] as $questionStat) {
                fputcsv($file, []);
                fputcsv($file, ['Pregunta: ' . $questionStat['question_text']]);
                fputcsv($file, ['Total votos: ' . $questionStat['total_votes']]);
                fputcsv($file, ['Opción', 'Votos', 'Porcentaje']);

                foreach ($questionStat['options'] as $option) {
                    fputcsv($file, [
                        $option['option_text'],
                        $option['votes'],
                        $option['percentage'] . '%'
                    ]);
                }
            }
            fputcsv($file, []);

            // Tokens duplicados del grupo
            if ($report['fraud_stats']['duplicate_token_stats']['total_tokens_with_duplicates'] > 0) {
                fputcsv($file, ['TOKENS CON INTENTOS DUPLICADOS (TODO EL GRUPO)']);
                fputcsv($file, ['Total tokens con duplicados', $report['fraud_stats']['duplicate_token_stats']['total_tokens_with_duplicates']]);
                fputcsv($file, ['Total intentos duplicados', $report['fraud_stats']['duplicate_token_stats']['total_duplicate_attempts']]);
                fputcsv($file, []);

                fputcsv($file, ['Token', 'Intentos', 'Estado', 'ID Encuesta', 'Último Intento']);
                foreach ($report['fraud_stats']['duplicate_token_stats']['top_duplicate_tokens'] as $tokenData) {
                    fputcsv($file, [
                        $tokenData['token'],
                        $tokenData['attempts'],
                        $tokenData['status'],
                        $tokenData['survey_id'],
                        $tokenData['last_attempt_at'] ?? 'N/A'
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar todos los votos de una encuesta a Excel/CSV
     * Incluye: Token, respuestas a todas las preguntas, hora, IP, fingerprint, estado
     */
    public function exportVotesExcel(Survey $survey)
    {
        $result = $this->voteExcelExporter->exportSurveyVotes($survey);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Exportar solo votos válidos y aprobados a Excel/CSV
     */
    public function exportValidVotesExcel(Survey $survey)
    {
        $result = $this->voteExcelExporter->exportValidVotes($survey);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Mostrar vista con tokens sospechosos (mismo minuto, 1 minuto diff, 2 minutos diff)
     */
    public function showSuspiciousTokens(Survey $survey)
    {
        $suspiciousData = $this->suspiciousTokenDetector->detectSuspiciousTokens($survey);

        return view('admin.reports.suspicious-tokens', [
            'survey' => $survey,
            'suspiciousData' => $suspiciousData,
        ]);
    }

    /**
     * Exportar tokens sospechosos a CSV
     */
    public function exportSuspiciousTokens(Survey $survey)
    {
        $result = $this->suspiciousTokenDetector->exportSuspiciousTokensToCSV($survey);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Exportar CSV excluyendo tokens sospechosos (sin modificar BD)
     */
    public function exportCleanVotes(Survey $survey)
    {
        $result = $this->voteExcelExporter->exportVotesExcludingSuspicious($survey);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Exportar estadísticas con datos para gráficos (con fotos y barras)
     */
    public function exportVoteStatistics(Survey $survey)
    {
        $result = $this->voteExcelExporter->exportVoteStatistics($survey);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Exportar todos los votos de un grupo consolidado a Excel/CSV
     */
    public function exportGroupVotesExcel(SurveyGroup $group)
    {
        $result = $this->voteExcelExporter->exportGroupVotes($group);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Exportar solo votos válidos de un grupo consolidado a Excel/CSV
     */
    public function exportGroupValidVotesExcel(SurveyGroup $group)
    {
        $result = $this->voteExcelExporter->exportGroupValidVotes($group);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Mostrar vista con tokens sospechosos de un grupo consolidado
     */
    public function showGroupSuspiciousTokens(SurveyGroup $group)
    {
        $suspiciousData = $this->suspiciousTokenDetector->detectSuspiciousTokensInGroup($group);

        return view('admin.reports.suspicious-tokens-group', [
            'group' => $group,
            'suspiciousData' => $suspiciousData,
        ]);
    }

    /**
     * Exportar tokens sospechosos de un grupo a CSV
     */
    public function exportGroupSuspiciousTokens(SurveyGroup $group)
    {
        $result = $this->suspiciousTokenDetector->exportSuspiciousTokensInGroupToCSV($group);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }

    /**
     * Exportar CSV de grupo excluyendo tokens sospechosos (sin modificar BD)
     */
    public function exportGroupCleanVotes(SurveyGroup $group)
    {
        $result = $this->voteExcelExporter->exportGroupVotesExcludingSuspicious($group);

        return response()->download($result['filepath'], $result['filename'])->deleteFileAfterSend(true);
    }
}
