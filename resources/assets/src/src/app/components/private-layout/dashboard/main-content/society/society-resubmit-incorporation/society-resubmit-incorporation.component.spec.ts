import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SocietyResubmitIncorporationComponent } from './society-resubmit-incorporation.component';

describe('SocietyResubmitIncorporationComponent', () => {
  let component: SocietyResubmitIncorporationComponent;
  let fixture: ComponentFixture<SocietyResubmitIncorporationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SocietyResubmitIncorporationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SocietyResubmitIncorporationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
