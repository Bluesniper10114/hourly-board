import { async, TestBed } from '@angular/core/testing';
import { PlanningModule } from './planning.module';

describe('PlanningModule', () => {
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      imports: [PlanningModule]
    }).compileComponents();
  }));

  it('should create', () => {
    expect(PlanningModule).toBeDefined();
  });
});
