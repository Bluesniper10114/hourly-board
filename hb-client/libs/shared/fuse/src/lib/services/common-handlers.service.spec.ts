import { TestBed } from '@angular/core/testing';

import { CommonHandlersService } from './common-handlers.service';

describe('CommonHandlersService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: CommonHandlersService = TestBed.get(CommonHandlersService);
    expect(service).toBeTruthy();
  });
});
