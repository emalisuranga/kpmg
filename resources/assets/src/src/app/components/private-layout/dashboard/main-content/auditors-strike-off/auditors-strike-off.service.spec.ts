import { TestBed, inject } from '@angular/core/testing';

import { AuditorsStrikeOffService } from './auditors-strike-off.service';

describe('AuditorsStrikeOffService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [AuditorsStrikeOffService]
    });
  });

  it('should be created', inject([AuditorsStrikeOffService], (service: AuditorsStrikeOffService) => {
    expect(service).toBeTruthy();
  }));
});
