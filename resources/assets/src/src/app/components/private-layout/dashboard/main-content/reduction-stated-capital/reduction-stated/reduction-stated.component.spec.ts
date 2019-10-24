import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReductionStatedComponent } from './reduction-stated.component';

describe('ReductionStatedComponent', () => {
  let component: ReductionStatedComponent;
  let fixture: ComponentFixture<ReductionStatedComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReductionStatedComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReductionStatedComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
