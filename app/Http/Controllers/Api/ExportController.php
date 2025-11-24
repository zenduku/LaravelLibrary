<?php

namespace App\Http\Controllers\Api;

use App\Author;
use App\Book;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export authors and books to XLSX file
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToXlsx()
    {
        $authors = Author::with('books')->get();
        $books = Book::with('author')->get();

        $spreadsheet = new Spreadsheet();

        // Authors Sheet
        $authorsSheet = $spreadsheet->getActiveSheet();
        $authorsSheet->setTitle('Authors');
        
        // Headers
        $authorsSheet->setCellValue('A1', 'ID');
        $authorsSheet->setCellValue('B1', 'Name');
        $authorsSheet->setCellValue('C1', 'Books Count');
        $authorsSheet->setCellValue('D1', 'Created At');
        $authorsSheet->setCellValue('E1', 'Updated At');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $authorsSheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // Authors data
        $row = 2;
        foreach ($authors as $author) {
            $authorsSheet->setCellValue('A' . $row, $author->id);
            $authorsSheet->setCellValue('B' . $row, $author->name);
            $authorsSheet->setCellValue('C' . $row, $author->books_count);
            $authorsSheet->setCellValue('D' . $row, $author->created_at->format('Y-m-d H:i:s'));
            $authorsSheet->setCellValue('E' . $row, $author->updated_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $authorsSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Books Sheet
        $booksSheet = $spreadsheet->createSheet();
        $booksSheet->setTitle('Books');

        // Headers
        $booksSheet->setCellValue('A1', 'ID');
        $booksSheet->setCellValue('B1', 'Title');
        $booksSheet->setCellValue('C1', 'Publication Year');
        $booksSheet->setCellValue('D1', 'Author ID');
        $booksSheet->setCellValue('E1', 'Author Name');
        $booksSheet->setCellValue('F1', 'Created At');
        $booksSheet->setCellValue('G1', 'Updated At');

        // Style headers
        $booksSheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Books data
        $row = 2;
        foreach ($books as $book) {
            $booksSheet->setCellValue('A' . $row, $book->id);
            $booksSheet->setCellValue('B' . $row, $book->title);
            $booksSheet->setCellValue('C' . $row, $book->publication_date ? $book->publication_date : 'N/A');
            $booksSheet->setCellValue('D' . $row, $book->author_id);
            $booksSheet->setCellValue('E' . $row, $book->author ? $book->author->name : 'N/A');
            $booksSheet->setCellValue('F' . $row, $book->created_at->format('Y-m-d H:i:s'));
            $booksSheet->setCellValue('G' . $row, $book->updated_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $booksSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set active sheet back to Authors
        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'library_export_' . date('Y-m-d_His') . '.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
