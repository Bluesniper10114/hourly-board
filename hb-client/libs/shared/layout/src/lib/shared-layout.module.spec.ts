import { async, TestBed } from '@angular/core/testing';
import { SharedLayoutModule } from './shared-layout.module';

describe('SharedLayoutModule', () => {
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      imports: [SharedLayoutModule]
    }).compileComponents();
  }));

  it('should create', () => {
    expect(SharedLayoutModule).toBeDefined();
  });
});
