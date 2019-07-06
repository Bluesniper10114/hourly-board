import { async, TestBed } from '@angular/core/testing';
import { SharedFuseModule } from './shared-fuse.module';

describe('SharedFuseModule', () => {
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      imports: [SharedFuseModule]
    }).compileComponents();
  }));

  it('should create', () => {
    expect(SharedFuseModule).toBeDefined();
  });
});
