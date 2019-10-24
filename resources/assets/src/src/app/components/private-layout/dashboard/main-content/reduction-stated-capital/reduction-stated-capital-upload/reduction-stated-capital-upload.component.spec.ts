import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReductionStatedCapitalUploadComponent } from './reduction-stated-capital-upload.component';

describe('ReductionStatedCapitalUploadComponent', () => {
  let component: ReductionStatedCapitalUploadComponent;
  let fixture: ComponentFixture<ReductionStatedCapitalUploadComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReductionStatedCapitalUploadComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReductionStatedCapitalUploadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
