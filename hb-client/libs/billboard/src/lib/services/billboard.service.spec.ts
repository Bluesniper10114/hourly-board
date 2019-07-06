import { TestBed } from '@angular/core/testing';

import { BillboardService } from './billboard.service';

describe('BillboardService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: BillboardService = TestBed.get(BillboardService);
    expect(service).toBeTruthy();
  });
});
