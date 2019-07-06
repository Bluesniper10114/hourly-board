import { async, TestBed } from '@angular/core/testing';
import { BillboardModule } from './billboard.module';

describe('BillboardModule', () => {
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      imports: [BillboardModule]
    }).compileComponents();
  }));

  it('should create', () => {
    expect(BillboardModule).toBeDefined();
  });
});
