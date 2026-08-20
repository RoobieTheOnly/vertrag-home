<?php

declare(strict_types=1);

function document_storage_root(): string
{
    return BASE_PATH . '/storage/documents';
}

function document_max_upload_bytes(): int
{
    return 20 * 1024 * 1024;
}

function allowed_document_types(): array
{
    return [
        'pdf' => ['application/pdf'],
        'doc' => [
            'application/msword',
            'application/CDFV2',
            'application/x-ole-storage',
            'application/octet-stream',
        ],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ],
        'xls' => [
            'application/vnd.ms-excel',
            'application/CDFV2',
            'application/x-ole-storage',
            'application/octet-stream',
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/octet-stream',
        ],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'txt' => ['text/plain'],
    ];
}

function document_preview_mode(
    array $document
): string {
    $extension =
        strtolower(
            (string) (
                $document[
                    'file_extension'
                ] ?? ''
            )
        );

    return match ($extension) {
        'pdf' =>
            'pdf',

        'jpg',
        'jpeg',
        'png' =>
            'image',

        'txt' =>
            'text',

        'doc',
        'docx',
        'xls',
        'xlsx' =>
            'office',

        default =>
            'unsupported',
    };
}

function document_is_previewable(
    array $document
): bool {
    return document_preview_mode(
        $document
    ) !== 'unsupported';
}


function document_preview_cache_root(): string
{
    return sys_get_temp_dir()
        . '/vertrag-home-preview';
}

function document_preview_cache_key(
    array $document,
    string $filePath
): string {
    $checksum =
        trim(
            (string) (
                $document[
                    'checksum_sha256'
                ] ?? ''
            )
        );

    if ($checksum !== '') {
        return preg_replace(
            '/[^a-f0-9]/i',
            '',
            $checksum
        ) ?: sha1($filePath);
    }

    return sha1(
        $filePath
        . '|'
        . (string) @filemtime(
            $filePath
        )
    );
}

function ensure_document_preview_cache(): string
{
    $root =
        document_preview_cache_root();

    if (
        !is_dir($root)
        && !mkdir(
            $root,
            0770,
            true
        )
        && !is_dir($root)
    ) {
        throw new RuntimeException(
            'Der Vorschau-Cache konnte nicht angelegt werden.'
        );
    }

    return $root;
}

function document_preview_pdf_path(
    array $document,
    string $filePath
): ?string {
    $mode =
        document_preview_mode(
            $document
        );

    if ($mode === 'pdf') {
        return $filePath;
    }

    if ($mode !== 'office') {
        return null;
    }

    if (
        !is_executable(
            '/usr/bin/soffice'
        )
        && !is_executable(
            '/usr/bin/libreoffice'
        )
    ) {
        return null;
    }

    $cacheRoot =
        ensure_document_preview_cache();

    $cacheKey =
        document_preview_cache_key(
            $document,
            $filePath
        );

    $workDirectory =
        $cacheRoot
        . '/'
        . $cacheKey;

    if (
        !is_dir($workDirectory)
        && !mkdir(
            $workDirectory,
            0770,
            true
        )
        && !is_dir($workDirectory)
    ) {
        return null;
    }

    $cachedPdf =
        $workDirectory
        . '/preview.pdf';

    if (
        is_file($cachedPdf)
        && filesize($cachedPdf) > 0
        && filemtime($cachedPdf)
            >= filemtime($filePath)
    ) {
        return $cachedPdf;
    }

    foreach (
        glob(
            $workDirectory
            . '/*.pdf'
        ) ?: []
        as $oldPdf
    ) {
        @unlink($oldPdf);
    }

    $binary =
        is_executable(
            '/usr/bin/soffice'
        )
            ? '/usr/bin/soffice'
            : '/usr/bin/libreoffice';

    $command =
        escapeshellcmd(
            $binary
        )
        . ' --headless'
        . ' --convert-to pdf'
        . ' --outdir '
        . escapeshellarg(
            $workDirectory
        )
        . ' '
        . escapeshellarg(
            $filePath
        )
        . ' 2>&1';

    $output = [];
    $exitCode = 1;

    exec(
        $command,
        $output,
        $exitCode
    );

    if ($exitCode !== 0) {
        return null;
    }

    $createdPdfs =
        glob(
            $workDirectory
            . '/*.pdf'
        ) ?: [];

    if ($createdPdfs === []) {
        return null;
    }

    $createdPdf =
        $createdPdfs[0];

    if (
        $createdPdf !== $cachedPdf
        && !@rename(
            $createdPdf,
            $cachedPdf
        )
    ) {
        return null;
    }

    return (
        is_file($cachedPdf)
        && filesize($cachedPdf) > 0
    )
        ? $cachedPdf
        : null;
}

function document_pdf_page_count(
    string $pdfPath
): ?int {
    if (
        !is_executable(
            '/usr/bin/pdfinfo'
        )
    ) {
        return null;
    }

    $command =
        'LC_ALL=C /usr/bin/pdfinfo '
        . escapeshellarg(
            $pdfPath
        )
        . ' 2>/dev/null';

    $output = [];
    $exitCode = 1;

    exec(
        $command,
        $output,
        $exitCode
    );

    if ($exitCode !== 0) {
        return null;
    }

    foreach ($output as $line) {
        if (
            preg_match(
                '/^Pages:\\s+(\\d+)$/',
                trim($line),
                $matches
            )
        ) {
            return max(
                1,
                (int) $matches[1]
            );
        }
    }

    return null;
}

function document_render_pdf_page_png(
    array $document,
    string $pdfPath,
    int $page
): ?string {
    if (
        $page < 1
        || !is_executable(
            '/usr/bin/pdftoppm'
        )
    ) {
        return null;
    }

    $cacheRoot =
        ensure_document_preview_cache();

    $cacheKey =
        document_preview_cache_key(
            $document,
            $pdfPath
        );

    $pageDirectory =
        $cacheRoot
        . '/'
        . $cacheKey
        . '/pages';

    if (
        !is_dir($pageDirectory)
        && !mkdir(
            $pageDirectory,
            0770,
            true
        )
        && !is_dir($pageDirectory)
    ) {
        return null;
    }

    $pageFile =
        $pageDirectory
        . '/page-'
        . $page
        . '.png';

    if (
        is_file($pageFile)
        && filesize($pageFile) > 0
    ) {
        return $pageFile;
    }

    $prefix =
        $pageDirectory
        . '/page-'
        . $page;

    $command =
        '/usr/bin/pdftoppm'
        . ' -f '
        . $page
        . ' -l '
        . $page
        . ' -singlefile'
        . ' -png'
        . ' -r 150 '
        . escapeshellarg(
            $pdfPath
        )
        . ' '
        . escapeshellarg(
            $prefix
        )
        . ' 2>/dev/null';

    $output = [];
    $exitCode = 1;

    exec(
        $command,
        $output,
        $exitCode
    );

    if (
        $exitCode !== 0
        || !is_file(
            $pageFile
        )
        || filesize(
            $pageFile
        ) <= 0
    ) {
        return null;
    }

    return $pageFile;
}




function upload_error_message(
    int $error
): string {
    return match ($error) {
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE =>
            'Die Datei ist zu groß.',
        UPLOAD_ERR_PARTIAL =>
            'Die Datei wurde nur teilweise hochgeladen.',
        UPLOAD_ERR_NO_FILE =>
            'Bitte wählen Sie eine Datei aus.',
        UPLOAD_ERR_NO_TMP_DIR =>
            'Temporäres Upload-Verzeichnis fehlt.',
        UPLOAD_ERR_CANT_WRITE =>
            'Die Datei konnte nicht gespeichert werden.',
        UPLOAD_ERR_EXTENSION =>
            'Der Upload wurde durch eine PHP-Erweiterung gestoppt.',
        default =>
            'Beim Hochladen ist ein unbekannter Fehler aufgetreten.',
    };
}

function validate_uploaded_document(
    array $file
): array {
    $error = (int) (
        $file['error'] ?? UPLOAD_ERR_NO_FILE
    );

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException(
            upload_error_message($error)
        );
    }

    $temporaryFile =
        (string) ($file['tmp_name'] ?? '');

    if (
        $temporaryFile === ''
        || !is_uploaded_file($temporaryFile)
    ) {
        throw new RuntimeException(
            'Die hochgeladene Datei ist ungültig.'
        );
    }

    $fileSize =
        (int) ($file['size'] ?? 0);

    if ($fileSize <= 0) {
        throw new RuntimeException(
            'Die hochgeladene Datei ist leer.'
        );
    }

    if ($fileSize > document_max_upload_bytes()) {
        throw new RuntimeException(
            'Die Datei darf maximal 20 MB groß sein.'
        );
    }

    $originalFilename =
        basename(
            str_replace(
                '\\',
                '/',
                (string) ($file['name'] ?? '')
            )
        );

    if ($originalFilename === '') {
        throw new RuntimeException(
            'Der Dateiname ist ungültig.'
        );
    }

    $extension = strtolower(
        pathinfo(
            $originalFilename,
            PATHINFO_EXTENSION
        )
    );

    $allowedTypes =
        allowed_document_types();

    if (
        $extension === ''
        || !array_key_exists(
            $extension,
            $allowedTypes
        )
    ) {
        throw new RuntimeException(
            'Dieser Dateityp ist nicht erlaubt.'
        );
    }

    $fileInfo = new finfo(
        FILEINFO_MIME_TYPE
    );

    $mimeType = $fileInfo->file(
        $temporaryFile
    );

    if (
        !is_string($mimeType)
        || $mimeType === ''
    ) {
        throw new RuntimeException(
            'Der Dateityp konnte nicht ermittelt werden.'
        );
    }

    if (
        !in_array(
            $mimeType,
            $allowedTypes[$extension],
            true
        )
    ) {
        throw new RuntimeException(
            'Dateiendung und tatsächlicher Dateityp stimmen nicht überein.'
        );
    }

    return [
        'temporary_file' => $temporaryFile,
        'original_filename' =>
            substr($originalFilename, 0, 255),
        'extension' => $extension,
        'mime_type' => $mimeType,
        'file_size' => $fileSize,
    ];
}

function store_uploaded_document(
    array $file,
    int $contractId,
    int $userId,
    ?string $documentName = null,
    ?int $documentTypeId = null,
    ?string $documentDate = null,
    ?int $replacesDocumentId = null
): int {
    if ($documentTypeId === null) {
        $defaultType = db()->query(
            '
            SELECT id
            FROM document_types
            WHERE name = "Sonstiges"
              AND is_active = 1
            LIMIT 1
            '
        )->fetchColumn();

        if ($defaultType) {
            $documentTypeId =
                (int) $defaultType;
        }
    }

    if ($documentTypeId !== null) {
        $typeCheck = db()->prepare(
            '
            SELECT id
            FROM document_types
            WHERE id = :id
              AND is_active = 1
            LIMIT 1
            '
        );

        $typeCheck->execute([
            'id' => $documentTypeId,
        ]);

        if (!$typeCheck->fetchColumn()) {
            throw new RuntimeException(
                'Die ausgewählte Dokumentart ist nicht verfügbar.'
            );
        }
    }

    if (
        $documentDate !== null
        && trim($documentDate) !== ''
        && DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $documentDate
        ) === false
    ) {
        throw new RuntimeException(
            'Das Dokumentdatum ist ungültig.'
        );
    }

    $versionNo = 1;

    if ($replacesDocumentId !== null) {
        $replacementCheck = db()->prepare(
            '
            SELECT
                id,
                version_no,
                document_type_id
            FROM contract_documents
            WHERE id = :id
              AND contract_id = :contract_id
              AND deleted_at IS NULL
            LIMIT 1
            '
        );

        $replacementCheck->execute([
            'id' =>
                $replacesDocumentId,
            'contract_id' =>
                $contractId,
        ]);

        $replacedDocument =
            $replacementCheck->fetch();

        if (!$replacedDocument) {
            throw new RuntimeException(
                'Das zu ersetzende Dokument wurde nicht gefunden.'
            );
        }

        $versionNo =
            max(
                1,
                (int) (
                    $replacedDocument[
                        'version_no'
                    ] ?? 1
                ) + 1
            );

        if (
            $documentTypeId === null
            && $replacedDocument[
                'document_type_id'
            ] !== null
        ) {
            $documentTypeId =
                (int) $replacedDocument[
                    'document_type_id'
                ];
        }
    }

    $validated =
        validate_uploaded_document($file);

    $relativeDirectory =
        'contracts/' . $contractId;

    $absoluteDirectory =
        document_storage_root()
        . '/'
        . $relativeDirectory;

    if (
        !is_dir($absoluteDirectory)
        && !mkdir(
            $absoluteDirectory,
            0770,
            true
        )
        && !is_dir($absoluteDirectory)
    ) {
        throw new RuntimeException(
            'Das Dokumentverzeichnis konnte nicht erstellt werden.'
        );
    }

    $storedFilename =
        bin2hex(random_bytes(24))
        . '.'
        . $validated['extension'];

    $relativePath =
        $relativeDirectory
        . '/'
        . $storedFilename;

    $absolutePath =
        $absoluteDirectory
        . '/'
        . $storedFilename;

    if (
        !move_uploaded_file(
            $validated['temporary_file'],
            $absolutePath
        )
    ) {
        throw new RuntimeException(
            'Die Datei konnte nicht dauerhaft gespeichert werden.'
        );
    }

    $checksum =
        hash_file(
            'sha256',
            $absolutePath
        );

    try {
        $stmt = db()->prepare(
            '
            INSERT INTO contract_documents (
                contract_id,
                document_type_id,
                document_name,
                document_date,
                version_no,
                replaces_document_id,
                is_current,
                original_filename,
                stored_filename,
                storage_path,
                mime_type,
                file_extension,
                file_size,
                checksum_sha256,
                uploaded_by
            )
            VALUES (
                :contract_id,
                :document_type_id,
                :document_name,
                :document_date,
                :version_no,
                :replaces_document_id,
                1,
                :original_filename,
                :stored_filename,
                :storage_path,
                :mime_type,
                :file_extension,
                :file_size,
                :checksum_sha256,
                :uploaded_by
            )
            '
        );

        $stmt->execute([
            'contract_id' => $contractId,
            'document_type_id' =>
                $documentTypeId,
            'document_name' =>
                $documentName !== null
                && trim($documentName) !== ''
                    ? trim($documentName)
                    : null,
            'document_date' =>
                $documentDate !== null
                && trim($documentDate) !== ''
                    ? trim($documentDate)
                    : null,
            'version_no' =>
                $versionNo,
            'replaces_document_id' =>
                $replacesDocumentId,
            'original_filename' =>
                $validated['original_filename'],
            'stored_filename' =>
                $storedFilename,
            'storage_path' =>
                $relativePath,
            'mime_type' =>
                $validated['mime_type'],
            'file_extension' =>
                $validated['extension'],
            'file_size' =>
                $validated['file_size'],
            'checksum_sha256' =>
                $checksum ?: null,
            'uploaded_by' =>
                $userId,
        ]);

        if ($replacesDocumentId !== null) {
            $markOld = db()->prepare(
                '
                UPDATE contract_documents
                SET is_current = 0
                WHERE id = :id
                  AND contract_id = :contract_id
                '
            );

            $markOld->execute([
                'id' =>
                    $replacesDocumentId,
                'contract_id' =>
                    $contractId,
            ]);
        }
    } catch (Throwable $e) {
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }

        throw $e;
    }

    return (int) db()->lastInsertId();
}

function resolve_document_path(
    string $relativePath
): ?string {
    $root =
        realpath(document_storage_root());

    if ($root === false) {
        return null;
    }

    $file =
        realpath(
            document_storage_root()
            . '/'
            . $relativePath
        );

    if ($file === false) {
        return null;
    }

    $expectedPrefix =
        rtrim(
            $root,
            DIRECTORY_SEPARATOR
        )
        . DIRECTORY_SEPARATOR;

    if (
        !str_starts_with(
            $file,
            $expectedPrefix
        )
    ) {
        return null;
    }

    return is_file($file)
        ? $file
        : null;
}


function remove_directory_if_empty(
    string $directory
): bool {
    if (!is_dir($directory)) {
        return true;
    }

    $entries = scandir($directory);

    if ($entries === false) {
        return false;
    }

    $remaining = array_values(
        array_diff(
            $entries,
            ['.', '..']
        )
    );

    if ($remaining !== []) {
        return false;
    }

    return @rmdir($directory);
}
