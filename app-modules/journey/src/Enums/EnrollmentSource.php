<?php

declare(strict_types=1);

namespace CampusOs\Journey\Enums;

/** De qual documento esta linha do histórico veio. */
enum EnrollmentSource: string
{
    case Transcript = 'transcript';          // histórico escolar
    case EnrollmentRequest = 'enrollment_request'; // requerimento de matrícula
    case Manual = 'manual';                  // digitado na tela de conferência
}
