<?php

namespace App\Constants;

enum AuditActions: string
{
  case CREATED = 'created';
  case UPDATED = 'updated';
  case DELETED = 'deleted';
  case STATUS_CHANGED = 'status_changed';

  case PENDING = 'pending';
}
